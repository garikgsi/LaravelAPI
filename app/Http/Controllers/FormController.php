<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Common\ABPResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\ABPTable;
use App\Services\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;




class FormController extends Controller
{
    // наличие ошибки
    public $is_error = false;
    // массив ошибок
    public $errors = [];
    // класс документа
    public $doc_class = null;
    // экземпляр (модель) класса документа
    private $doc = null;
    // табличная часть документа
    private $doc_table = null;
    // итоговые значения для формы
    private $itogs = null;
    // тип вывода (html, pdf, email)
    private $view = 'html';
    // тип формы (может быть несколько у разных документов, например, акт, торг-12, счет-фактура и тп) - соответсвует шаблону blade
    private $form_name = 'main';
    // pdf контроллер генерации
    protected $pdf;
    // ответ сервера
    private $response;


    // конструктор
    public function __construct(Pdf $pdf)
    {
        // создаем экземпляр ответа сервера
        $this->response = new ABPResponse();
        // обрабатываем параметры запроса
        if (Route::current()) {
            // таблица - параметр маршрута
            $table = trim(urldecode(Route::current()->parameter('table')));
            $id = Route::current()->parameter('id');
            if (Route::current()->hasParameter('view')) {
                $this->view = Route::current()->parameter('view');
            }
            if (isset($table) && isset($id)) {
                // создаем экземпляр таблицы АБП
                $abp_table = new ABPTable($table);
                // если таблица существует
                if ($abp_table->table_exists()) {
                    $this->doc_class = $abp_table->class_name();
                    $class_name = $this->doc_class;
                    $doc = new $class_name();
                    $this->doc = $doc->where('id', $id)->first();
                    if ($this->doc) {
                        if (method_exists($this->doc, 'pf_data')) {
                            $additional = $this->doc->pf_data();
                            // dd($additional["table"]);
                            if (isset($additional["doc"])) $this->doc = $additional["doc"];
                            $this->doc_table = $additional["table"];
                            $this->itogs = $additional["itogs"];
                        }
                        $this->pdf = $pdf;
                    } else {
                        $this->add_error('В таблице [' . $table . '] не существует записи [' . $id . ']');
                    }
                } else {
                    $this->add_error('Таблицы [' . $table . '] не существует в базе данных');
                }
            } else {
                $this->add_error('Не переданы обязательные параметры');
            }
        }
    }

    public function get(Request $request, $table, $id)
    {
        if (!$this->is_error) {
            // проверим права на чтение
            $user = Auth::user();
            if ($user && $user->can('view', $this->doc)) {
                // форма в html
                $form_data = view('forms/' . $table . '/' . $this->form_name, ["doc" => (object)$this->doc, "doc_table" => $this->doc_table, "itogs" => (object)$this->itogs]);
                // формируем печатную форму в html
                $file_name = Str::random();
                $html_file_name = 'print_forms/' . $file_name . '.html';
                $res = Storage::put($html_file_name, $form_data);
                $html_form_url = asset("/print/" . $file_name);
                if ($res) {
                    // результат в зависимости от способа вывода
                    switch ($this->view) {
                            // форма в html
                        case 'html': {
                                return $this->response_file($html_form_url);
                            }
                            break;
                        case 'pdf': {
                                $pdf_file = $this->pdf->from_html_form($html_form_url, 'path');
                                // dd($pdf_file);
                                if ($pdf_file) {
                                    $pdf_file_name = 'print_forms/' . $file_name . '.pdf';
                                    $res = Storage::put($pdf_file_name, File::get($pdf_file));
                                    if ($res) {
                                        return $this->response_file(asset(Storage::url($pdf_file_name)));
                                    } else {
                                        $this->add_error('Файл PDF не удалось сохранить на ФС');
                                    }
                                    // return response($pdf_file, 200)->withHeaders([
                                    //     'Content-Type' => 'application/pdf',
                                    //     'Content-Disposition' => ($request->has('download') ? 'attachment' : 'inline') . "; filename={$table}-{$id}.pdf",
                                    // ]);

                                } else {
                                    $this->add_error('Файл PDF не сформирован');
                                }
                                // $pdf_file = $this->pdf->render($form_data, 'path');
                                // // dd($pdf_file);
                                // if ($pdf_file) {
                                //     $pdf_file_name = 'print_forms/' . $table . '-' . $id . '.pdf';
                                //     $res = Storage::put($pdf_file_name, File::get($pdf_file));
                                //     if ($res) {
                                //         return $this->response_file(asset(Storage::url($pdf_file_name)));
                                //     } else {
                                //         $this->add_error('Файл PDF не удалось сохранить на ФС');
                                //     }
                                //     // return response($pdf_file, 200)->withHeaders([
                                //     //     'Content-Type' => 'application/pdf',
                                //     //     'Content-Disposition' => ($request->has('download') ? 'attachment' : 'inline') . "; filename={$table}-{$id}.pdf",
                                //     // ]);

                                // } else {
                                //     $this->add_error('Файл PDF не сформирован');
                                // }
                            }
                            break;
                    }
                } else {
                    $this->add_error('Печатная форма не сформирована');
                }
            } else {
                $this->add_error('Недостаточно прав для просмотра печатной формы');
            }
        }
        if ($this->is_error) {
            return $this->response_error();
        }
    }

    public function form(Request $request, $document_id)
    {
        $doc_path = 'print_forms/' . $document_id . '.html';
        if (Storage::exists($doc_path)) {
            return Storage::get($doc_path);
        }
    }

    private function add_error($error)
    {
        $this->is_error = true;
        $this->errors[] = $error;
    }

    public function has_err()
    {
        return $this->is_error;
    }

    public function response_error()
    {
        return $this->response->set_err(implode(', ', $this->errors), 400)->response();
    }

    public function response_file($file)
    {
        return $this->response->set_data($file,  1, 200)->response();
    }
}