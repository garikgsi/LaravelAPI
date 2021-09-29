<?php
namespace App\Common;

use Illuminate\Database\Schema\Blueprint;
use App\ABPTable;


class CustomBlueprint extends Blueprint
{


    public function commonFields()
    {
        $this->bigIncrements('id');
        $this->uuid('uuid')->nullable();
        $this->string('name',1024)->default('');
        $this->string('comment',255)->nullable()->default('');
        $this->timestamp('created_at')->nullable();
        $this->unsignedBigInteger('created_by')->nullable();
        $this->timestamp('updated_at')->nullable();
        $this->unsignedBigInteger('updated_by')->nullable();
        // $this->timestamp('deleted_at')->nullable();
        $this->unsignedBigInteger('deleted_by')->nullable();
        // $this->boolean('is_deleted')->default(false);
        $this->boolean('is_protected')->default(false);
        $this->timestamp('sync_1c_at')->nullable();
        $this->index('uuid');
        $this->index('name');
        $this->index('comment');
        $this->index('created_at');
        $this->index('created_by');
        $this->index('updated_at');
        $this->index('updated_by');
        // $this->index('deleted_at');
        $this->index('deleted_by');
        // $this->index('is_deleted');
        $this->index('is_protected');
        $this->index('sync_1c_at');
    }

    public function mySQL() {
        $this->engine = 'InnoDB';
        $this->charset = 'utf8';
        $this->collation = 'utf8_general_ci';
    }

    public function documentFields() {
        $this->string('doc_num',32)->nullable()->default('')->index('doc_num');
        $this->date('doc_date')->nullable()->index('doc_date');
        $this->boolean('is_active')->default(0)->index('is_active');

    }
}

?>
