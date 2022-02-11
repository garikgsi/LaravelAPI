@extends('forms.skeleton')

@section('title')
    Счет на оплату
@endsection

@section('style')
<style>
body { background: #ffffff; margin: 0; font-family: Arial; font-size: 8pt; font-style: normal; }
tr.R0{ height: 15px; }
tr.R0 td.R10C1{ font-family: Arial; font-size: 14pt; font-style: normal; font-weight: bold; vertical-align: middle; }
tr.R0 td.R23C1{ text-align: center; vertical-align: top; overflow: hidden;border-left: #000000 2px solid; border-top: #000000 1px solid; }
tr.R0 td.R23C2{ text-align: left; vertical-align: top; border-left: #000000 1px solid; border-top: #000000 1px solid; }
tr.R0 td.R23C3{ text-align: right; vertical-align: top; border-left: #000000 1px solid; border-top: #000000 1px solid; }
tr.R0 td.R23C4{ text-align: left; vertical-align: top; border-left: #000000 1px solid; border-top: #000000 1px solid; }
tr.R0 td.R23C6{ text-align: right; vertical-align: top; border-left: #000000 1px solid; border-top: #000000 1px solid; border-right: #000000 2px solid; }
tr.R0 td.R39C6{ }
tr.R0 td.R7C23{ text-align: center; vertical-align: middle; border-left: #000000 1px solid; border-bottom: #000000 1px solid; border-right: #000000 1px solid; }
tr.R0 td.R8C1{ font-family: Arial; font-size: 8pt; font-style: normal; text-align: left; vertical-align: middle; border-left: #000000 1px solid; border-top: #ffffff 0px none; border-bottom: #000000 1px solid; }
tr.R12{ height: 9px; }
tr.R12 td.R12C1{ border-bottom: #000000 2px solid; }
tr.R12 td.R24C1{ border-top: #000000 2px solid; }
tr.R14{ height: 18px; }
tr.R14 td.R14C1{ font-family: Arial; font-size: 9pt; font-style: normal; vertical-align: top; }
tr.R14 td.R14C5{ font-family: Arial; font-size: 9pt; font-style: normal; font-weight: bold; vertical-align: top; }
tr.R2{ height: 20px; }
tr.R2 td.R2C1{ font-family: Arial; font-size: 9pt; font-style: normal; vertical-align: top; border-left: #000000 1px solid; border-top: #000000 1px solid; }
tr.R2 td.R2C20{ font-family: Arial; font-size: 9pt; font-style: normal; vertical-align: middle; border-left: #000000 1px solid; border-top: #000000 1px solid; border-bottom: #000000 1px solid; border-right: #000000 1px solid; }
tr.R2 td.R2C22{ font-family: Arial; font-size: 9pt; font-style: normal; text-align: left; vertical-align: middle; border-left: #000000 1px solid; border-top: #000000 1px solid; border-right: #000000 1px solid; }
tr.R2 td.R2C23{ border-left: #000000 1px solid; border-top: #000000 1px solid; border-right: #000000 1px solid; }
tr.R2 td.R3C20{ font-family: Arial; font-size: 9pt; font-style: normal; vertical-align: top; border-left: #000000 1px solid; border-top: #000000 1px solid; border-bottom: #000000 1px solid; border-right: #000000 1px solid; }
tr.R2 td.R3C22{ font-family: Arial; font-size: 9pt; font-style: normal; text-align: left; vertical-align: top; border-left: #000000 1px solid; border-bottom: #000000 1px solid; border-right: #000000 1px solid; }
tr.R2 td.R4C1{ border-left: #000000 1px solid; }
tr.R2 td.R5C1{ font-family: Arial; font-size: 9pt; font-style: normal; text-align: left; vertical-align: middle; border-left: #000000 1px solid; border-top: #000000 1px solid; }
tr.R2 td.R5C11{ font-family: Arial; font-size: 9pt; font-style: normal; text-align: left; vertical-align: middle; border-top: #000000 1px solid; }
tr.R2 td.R5C13{ font-family: Arial; font-size: 9pt; font-style: normal; text-align: left; vertical-align: middle; border-top: #000000 1px solid; }
tr.R2 td.R5C22{ font-family: Arial; font-size: 9pt; font-style: normal; text-align: left; vertical-align: top; border-left: #000000 1px solid; border-top: #000000 1px solid; border-bottom: #000000 1px solid; border-right: #000000 1px solid; }
tr.R2 td.R5C3{ font-family: Arial; font-size: 9pt; font-style: normal; text-align: left; vertical-align: middle; border-top: #000000 1px solid; border-right: #000000 1px solid; }
tr.R20{ height: 17px; }
tr.R20 td.R20C1{ font-family: Arial; font-size: 9pt; font-style: normal; vertical-align: top; }
tr.R20 td.R20C5{ font-family: Arial; font-size: 9pt; font-style: normal; font-weight: bold; vertical-align: top; }
tr.R20 td.R22C1{ font-family: Arial; font-size: 9pt; font-style: normal; font-weight: bold; text-align: center; vertical-align: middle; border-left: #000000 2px solid; border-top: #000000 2px solid; }
tr.R20 td.R22C2{ font-family: Arial; font-size: 9pt; font-style: normal; font-weight: bold; text-align: center; vertical-align: middle; border-left: #000000 1px solid; border-top: #000000 2px solid; }
tr.R20 td.R22C6{ font-family: Arial; font-size: 9pt; font-style: normal; font-weight: bold; text-align: center; vertical-align: middle; border-left: #000000 1px solid; border-top: #000000 2px solid; border-right: #000000 2px solid; }
tr.R20 td.R25C6{ font-family: Arial; font-size: 9pt; font-style: normal; font-weight: bold; text-align: right; vertical-align: top; }
tr.R20 td.R28C1{ font-family: Arial; font-size: 9pt; font-style: normal; }
tr.R20 td.R38C1{ font-family: Arial; font-size: 9pt; font-style: normal; font-weight: bold; }
tr.R20 td.R38C10{ text-align: right; border-bottom: #000000 1px solid; }
tr.R20 td.R38C6{ border-bottom: #000000 1px solid; }
tr.R30{ height: 10px; }
tr.R30 td.R30C1{ font-family: Arial; font-size: 9pt; font-style: normal; }
tr.R31{ height: 16px; }
tr.R31 td.R31C1{ font-family: Arial; font-size: 9pt; font-style: normal; }
tr.R31 td.R32C1{ font-family: Arial; font-size: 9pt; font-style: normal; text-align: left; }
tr.R34{ height: 31px; }
tr.R34 td.R34C1{ font-family: Arial; font-size: 9pt; font-style: normal; text-align: left; }
tr.R6{ height: 19px; }
tr.R6 td.R6C1{ font-family: Arial; font-size: 9pt; font-style: normal; text-align: left; vertical-align: top; border-left: #000000 1px solid; border-top: #000000 1px solid; }
table {table-layout: fixed; padding: 0px; padding-left: 2px; vertical-align:bottom; border-collapse:collapse;width: 100%; font-family: Arial; font-size: 8pt; font-style: normal; }
td { padding: 0px; padding-left: 2px; overflow:hidden; }
</style>
@endsection


@section('body')
<TABLE style="width:100%; height:0px; " CELLSPACING=0>
    <COL WIDTH=7>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=26>
    <COL WIDTH=15>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=17>
    <COL WIDTH=16>
    <COL WIDTH=16>
    <COL WIDTH=17>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=23>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=12>
    <COL WIDTH=13>
    <COL>
    <TR CLASS=R0>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD>&nbsp;</TD>
    </TR>
    </TABLE>
    <TABLE style="width:100%; height:0px; " CELLSPACING=0>
    <COL WIDTH=7>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=26>
    <COL WIDTH=15>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=15>
    <COL WIDTH=15>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=20>
    <COL WIDTH=17>
    <COL WIDTH=15>
    <COL WIDTH=17>
    <COL WIDTH=16>
    <COL WIDTH=16>
    <COL WIDTH=17>
    <COL WIDTH=20>
    <COL WIDTH=28>
    <COL WIDTH=146>
    <COL WIDTH=98>
    <COL>
    <TR CLASS=R0>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD>&nbsp;</TD>
    </TR>
    <TR CLASS=R2>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R2C1" COLSPAN=19 ROWSPAN=2>{{$doc->firm_bank}}</TD>
    <TD CLASS="R2C20" COLSPAN=2><SPAN STYLE="white-space:nowrap;max-width:0px;">БИК</SPAN></TD>
    <TD CLASS="R2C22"><SPAN STYLE="white-space:nowrap;max-width:0px;">{{$doc->firm_bik}}</SPAN></TD>

    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R2>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R3C20" COLSPAN=2 ROWSPAN=2><SPAN STYLE="white-space:nowrap;max-width:0px;">Сч.&nbsp;№</SPAN></TD>
    <TD CLASS="R3C22" ROWSPAN=2><SPAN STYLE="white-space:nowrap;max-width:0px;">{{$doc->firm_ks}}</SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R2>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R4C1" COLSPAN=19><SPAN STYLE="white-space:nowrap;max-width:0px;">Банк&nbsp;получателя</SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R2>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R5C1" COLSPAN=2><SPAN STYLE="white-space:nowrap;max-width:0px;">ИНН</SPAN></TD>
    <TD CLASS="R5C3" COLSPAN=8><SPAN STYLE="white-space:nowrap;max-width:0px;">{{$doc->firm_inn}}</SPAN></TD>
    <TD CLASS="R5C11" COLSPAN=2><SPAN STYLE="white-space:nowrap;max-width:0px;">КПП</SPAN></TD>
    <TD CLASS="R5C13" COLSPAN=7><SPAN STYLE="white-space:nowrap;max-width:0px;">{{$doc->firm_kpp}}</SPAN></TD>
    <TD CLASS="R3C20" COLSPAN=2 ROWSPAN=4><SPAN STYLE="white-space:nowrap;max-width:0px;">Сч.&nbsp;№</SPAN></TD>
    <TD CLASS="R5C22" ROWSPAN=4><SPAN STYLE="white-space:nowrap;max-width:0px;">{{$doc->firm_rs}}</SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R6>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R6C1" COLSPAN=19 ROWSPAN=2>{{$doc->firm_name}}</TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R0>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R0>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R8C1" COLSPAN=19><SPAN STYLE="white-space:nowrap;max-width:0px;">Получатель</SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R0>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD>&nbsp;</TD>
    </TR>
    </TABLE>
    <TABLE style="width:100%; height:0px; " CELLSPACING=0>
    <COL WIDTH=7>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=26>
    <COL WIDTH=15>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=17>
    <COL WIDTH=16>
    <COL WIDTH=16>
    <COL WIDTH=17>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=23>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=12>
    <COL WIDTH=13>
    <COL>
    <TR CLASS=R0>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R10C1" COLSPAN=32 ROWSPAN=2>Счет на оплату № {{$doc->doc_num}} от {{$doc->doc_date}}</TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R0>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD>&nbsp;</TD>
    </TR>
    <TR CLASS=R12>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1" COLSPAN=32><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="width:100%;height:9px;overflow:hidden;">&nbsp;</DIV></TD>
    </TR>
    <TR CLASS=R12>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="width:100%;height:9px;overflow:hidden;">&nbsp;</DIV></TD>
    </TR>
    <TR CLASS=R14>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R14C1" COLSPAN=4 ROWSPAN=2><SPAN STYLE="white-space:nowrap;max-width:0px;">Поставщик<BR>(Исполнитель):</SPAN></TD>
    <TD CLASS="R14C5" COLSPAN=28 ROWSPAN=2>{{$doc->firm_str}}</TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R0>
    <TD><DIV STYLE="position:relative; height:15px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:15px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:15px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="width:100%;height:15px;overflow:hidden;">&nbsp;</DIV></TD>
    </TR>
    <TR CLASS=R12>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="width:100%;height:9px;overflow:hidden;">&nbsp;</DIV></TD>
    </TR>
    <TR CLASS=R14>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R14C1" COLSPAN=4 ROWSPAN=2><SPAN STYLE="white-space:nowrap;max-width:0px;">Покупатель<BR>(Заказчик):</SPAN></TD>
    <TD CLASS="R14C5" COLSPAN=28 ROWSPAN=2>{{$doc->kontragent_str}}</TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R0>
    <TD><DIV STYLE="position:relative; height:15px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:15px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:15px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="width:100%;height:15px;overflow:hidden;">&nbsp;</DIV></TD>
    </TR>
    <TR CLASS=R12>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="width:100%;height:9px;overflow:hidden;">&nbsp;</DIV></TD>
    </TR>
    <TR CLASS=R20>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R20C1" COLSPAN=4><SPAN STYLE="white-space:nowrap;max-width:0px;">Основание:</SPAN></TD>
    <TD CLASS="R20C5" COLSPAN=28>{{$doc->osnovanie}}</TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R12>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="width:100%;height:9px;overflow:hidden;">&nbsp;</DIV></TD>
    </TR>
    </TABLE>
    <TABLE style="width:100%; height:0px; " CELLSPACING=0>
    <COL WIDTH=7>
    <COL WIDTH=32>
    <COL WIDTH=328>
    <COL WIDTH=54>
    <COL WIDTH=42>
    <COL WIDTH=87>
    <COL WIDTH=99>
    <COL>
    <TR CLASS=R20>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R22C1"><SPAN STYLE="white-space:nowrap;max-width:0px;">№</SPAN></TD>
    <TD CLASS="R22C2"><SPAN STYLE="white-space:nowrap;max-width:0px;">Товары&nbsp;(работы,&nbsp;услуги)</SPAN></TD>
    <TD CLASS="R22C2"><SPAN STYLE="white-space:nowrap;max-width:0px;">Кол-во</SPAN></TD>
    <TD CLASS="R22C2"><SPAN STYLE="white-space:nowrap;max-width:0px;">Ед.</SPAN></TD>
    <TD CLASS="R22C2"><SPAN STYLE="white-space:nowrap;max-width:0px;">Цена</SPAN></TD>
    <TD CLASS="R22C6"><SPAN STYLE="white-space:nowrap;max-width:0px;">Сумма</SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
        @if (isset($doc_table))
        @foreach ($doc_table as $row)

    <TR CLASS=R0>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R23C1"><SPAN STYLE="white-space:nowrap;max-width:0px;">{{$row->npp}}</SPAN></TD>
    <TD CLASS="R23C2">{{$row->name}}</TD>
    <TD CLASS="R23C3"><SPAN STYLE="white-space:nowrap;max-width:0px;">{{$row->kolvo}}</SPAN></TD>
    <TD CLASS="R23C4"><SPAN STYLE="white-space:nowrap;max-width:0px;">{{$row->ed_ism}}</SPAN></TD>
    <TD CLASS="R23C3"><SPAN STYLE="white-space:nowrap;max-width:0px;">{{$row->price}}</SPAN></TD>
    <TD CLASS="R23C6"><SPAN STYLE="white-space:nowrap;max-width:0px;">{{$row->summa}}</SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    @endforeach
    @endif
    </TABLE>
    <TABLE style="width:100%; height:0px; " CELLSPACING=0>
    <COL WIDTH=7>
    <COL WIDTH=32>
    <COL WIDTH=75>
    <COL WIDTH=190>
    <COL WIDTH=54>
    <COL WIDTH=42>
    <COL WIDTH=151>
    <COL WIDTH=98>
    <COL>
    <TR CLASS=R12>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R24C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R24C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R24C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R24C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R24C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R24C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R24C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="width:100%;height:9px;overflow:hidden;">&nbsp;</DIV></TD>
    </TR>
    <TR CLASS=R20>
    <TD CLASS="R25C6" COLSPAN=7><SPAN STYLE="white-space:nowrap;max-width:0px;">Итого:</SPAN></TD>
    <TD CLASS="R25C6"><SPAN STYLE="white-space:nowrap;max-width:0px;">{{$itogs->summa}}</SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R20>
    <TD CLASS="R25C6" COLSPAN=7><SPAN STYLE="white-space:nowrap;max-width:0px;">В&nbsp;том&nbsp;числе&nbsp;НДС:</SPAN></TD>
    <TD CLASS="R25C6"><SPAN STYLE="white-space:nowrap;max-width:0px;">{{$itogs->sum_nds}}</SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R20>
    <TD CLASS="R25C6" COLSPAN=7><SPAN STYLE="white-space:nowrap;max-width:0px;">Всего&nbsp;к&nbsp;оплате:</SPAN></TD>
    <TD CLASS="R25C6"><SPAN STYLE="white-space:nowrap;max-width:0px;">{{$itogs->summa}}</SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    </TABLE>
    <TABLE style="width:100%; height:0px; " CELLSPACING=0>
    <COL WIDTH=7>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=26>
    <COL WIDTH=15>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=17>
    <COL WIDTH=16>
    <COL WIDTH=16>
    <COL WIDTH=17>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=23>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=21>
    <COL WIDTH=12>
    <COL WIDTH=13>
    <COL>
    <TR CLASS=R20>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R28C1" COLSPAN=32><SPAN STYLE="white-space:nowrap;max-width:0px;">Всего&nbsp;наименований&nbsp;{{$itogs->kolvo}},&nbsp;на&nbsp;сумму&nbsp;{{$itogs->summa}}&nbsp;{{$doc->valuta}}</SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R20>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R20C5" COLSPAN=31>{{$itogs->summa_propis}}</TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    <TR CLASS=R30>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R30C1"><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:10px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="width:100%;height:10px;overflow:hidden;">&nbsp;</DIV></TD>
    </TR>

    <TR CLASS=R31>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R32C1" COLSPAN=32>{{$doc->comment}}</TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>

    <TR CLASS=R12>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD CLASS="R12C1"><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="position:relative; height:9px;width: 100%; overflow:hidden;"><SPAN></SPAN></DIV></TD>
    <TD><DIV STYLE="width:100%;height:9px;overflow:hidden;">&nbsp;</DIV></TD>
    </TR>

    <TR CLASS=R20>
    <TD><DIV STYLE="height:50px;overflow:hidden;">&nbsp;</DIV></TD>
    <TD CLASS="R38C1" COLSPAN=5><SPAN STYLE="white-space:nowrap;max-width:0px;">Руководитель</SPAN></TD>
    <TD CLASS="R38C6"><SPAN></SPAN></TD>
    <TD CLASS="R38C6"><SPAN></SPAN></TD>
    <TD CLASS="R38C6"><SPAN></SPAN></TD>
    <TD CLASS="R38C6"><SPAN></SPAN></TD>
    <TD CLASS="R38C10" COLSPAN=9>Мишуров Е. Е.</TD>
    <TD><SPAN></SPAN></TD>
    <TD CLASS="R38C1" COLSPAN=4><SPAN STYLE="white-space:nowrap;max-width:0px;">Бухгалтер</SPAN></TD>
    <TD CLASS="R38C6"><SPAN></SPAN></TD>
    <TD CLASS="R38C6"><SPAN></SPAN></TD>
    <TD CLASS="R38C6"><SPAN></SPAN></TD>
    <TD CLASS="R38C10" COLSPAN=6><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD><SPAN></SPAN></TD>
    <TD></TD>
    </TR>
    </TABLE>

@endsection


