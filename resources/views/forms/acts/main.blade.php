@extends('forms.skeleton')

@section('title')
    УПД
@endsection

@section('style')
<style id="upd_20196_Styles">
    <!--table
        {mso-displayed-decimal-separator:"\,";
        mso-displayed-thousand-separator:" ";}
    .xl6520196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:top;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl6620196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:top;
        border-top:none;
        border-right:none;
        border-bottom:1.0pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl6720196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:top;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl6820196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl6920196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        border-top:none;
        border-right:none;
        border-bottom:none;
        border-left:1.0pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl7020196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl7120196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl7220196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        border-top:none;
        border-right:none;
        border-bottom:1.0pt solid windowtext;
        border-left:1.0pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl7320196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:right;
        vertical-align:bottom;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl7420196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        border-top:none;
        border-right:none;
        border-bottom:none;
        border-left:1.0pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl7520196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:general;
        vertical-align:bottom;
        border-top:none;
        border-right:none;
        border-bottom:none;
        border-left:1.0pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl7620196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        border-top:none;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl7720196
        {color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        border-top:none;
        border-right:none;
        border-bottom:none;
        border-left:1.0pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;
        padding-left:15px;
        mso-char-indent-count:1;}
    .xl7820196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:Standard;
        text-align:center;
        vertical-align:middle;
        border:.5pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;
        mso-text-control:shrinktofit;}
    .xl7920196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:middle;
        border:.5pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;
        mso-text-control:shrinktofit;}
    .xl8020196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl8120196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:middle;
        border:.5pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl8220196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl8320196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:middle;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:.5pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl8420196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:middle;
        border-top:.5pt solid windowtext;
        border-right:.5pt solid windowtext;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl8520196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:middle;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl8620196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:middle;
        border-top:.5pt solid windowtext;
        border-right:.5pt solid windowtext;
        border-bottom:.5pt solid windowtext;
        border-left:1.0pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl8720196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\#\,\#\#0";
        text-align:center;
        vertical-align:middle;
        border:.5pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;
        mso-text-control:shrinktofit;}
    .xl8820196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl8920196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        border-top:none;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl9020196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl9120196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:9.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:General;
        text-align:center;
        vertical-align:bottom;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl9220196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:right;
        vertical-align:top;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl9320196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:right;
        vertical-align:top;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl9420196
        {color:windowtext;
        font-size:9.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:General;
        text-align:left;
        vertical-align:bottom;
        border-top:none;
        border-right:none;
        border-bottom:none;
        border-left:1.0pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;
        padding-left:15px;
        mso-char-indent-count:1;}
    .xl9520196
        {color:windowtext;
        font-size:9.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:General;
        text-align:left;
        vertical-align:bottom;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;
        padding-left:15px;
        mso-char-indent-count:1;}
    .xl9620196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:9.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        border-top:none;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl9720196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:9.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl9820196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Arial Cyr";
        mso-generic-font-family:auto;
        mso-font-charset:204;
        mso-number-format:General;
        text-align:left;
        vertical-align:top;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl9920196
        {color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;
        padding-left:15px;
        mso-char-indent-count:1;}
    .xl10020196
        {color:windowtext;
        font-size:8.5pt;
        font-weight:700;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        border-top:none;
        border-right:none;
        border-bottom:none;
        border-left:1.0pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;
        padding-left:15px;
        mso-char-indent-count:1;}
    .xl10120196
        {color:windowtext;
        font-size:8.5pt;
        font-weight:700;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;
        padding-left:15px;
        mso-char-indent-count:1;}
    .xl10220196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        border-top:none;
        border-right:1.0pt solid windowtext;
        border-bottom:none;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl10320196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        border-top:1.0pt solid windowtext;
        border-right:none;
        border-bottom:1.0pt solid windowtext;
        border-left:1.0pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl10420196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        border-top:1.0pt solid windowtext;
        border-right:1.0pt solid windowtext;
        border-bottom:1.0pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl10520196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:middle;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:none;
        border-left:.5pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl10620196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:middle;
        border-top:.5pt solid windowtext;
        border-right:.5pt solid windowtext;
        border-bottom:none;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl10720196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:middle;
        border-top:none;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:.5pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl10820196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:middle;
        border-top:none;
        border-right:.5pt solid windowtext;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl10920196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl11020196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:middle;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:none;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl11120196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:middle;
        border-top:none;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl11220196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        border-top:none;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl11320196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:none;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl11420196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:none;
        border-left:1.0pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl11520196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:700;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:middle;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:1.0pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl11620196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:700;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:middle;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl11720196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:700;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:middle;
        border-top:.5pt solid windowtext;
        border-right:.5pt solid windowtext;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl11820196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:Standard;
        text-align:center;
        vertical-align:middle;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:.5pt solid windowtext;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;
        mso-text-control:shrinktofit;}
    .xl11920196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:Standard;
        text-align:center;
        vertical-align:middle;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;
        mso-text-control:shrinktofit;}
    .xl12020196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:Standard;
        text-align:center;
        vertical-align:middle;
        border-top:.5pt solid windowtext;
        border-right:.5pt solid windowtext;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;
        mso-text-control:shrinktofit;}
    .xl12120196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:middle;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;
        mso-text-control:shrinktofit;}
    .xl12220196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        border-top:none;
        border-right:none;
        border-bottom:1.0pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl12320196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:top;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:1.0pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl12420196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:bottom;
        border-top:none;
        border-right:none;
        border-bottom:1.0pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl12520196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:top;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:none;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl12620196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        border-top:none;
        border-right:1.0pt solid windowtext;
        border-bottom:none;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl12720196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl12820196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        border-top:none;
        border-right:1.0pt solid windowtext;
        border-bottom:none;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl12920196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:top;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:none;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl13020196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:none;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl13120196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:top;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl13220196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:7.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:top;
        border-top:none;
        border-right:1.0pt solid windowtext;
        border-bottom:none;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:normal;}
    .xl13320196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.5pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:left;
        vertical-align:middle;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl13420196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        border-top:none;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl13520196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:General;
        text-align:center;
        vertical-align:bottom;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:none;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    .xl13620196
        {padding-top:1px;
        padding-right:1px;
        padding-left:1px;
        mso-ignore:padding;
        color:windowtext;
        font-size:8.0pt;
        font-weight:400;
        font-style:normal;
        text-decoration:none;
        font-family:"Times New Roman", serif;
        mso-font-charset:204;
        mso-number-format:"\@";
        text-align:center;
        vertical-align:bottom;
        border-top:.5pt solid windowtext;
        border-right:none;
        border-bottom:.5pt solid windowtext;
        border-left:none;
        mso-background-source:auto;
        mso-pattern:auto;
        white-space:nowrap;}
    -->
    </style>
@endsection


@section('body')
<div id="upd_20196" align=center x:publishsource="Excel">

<table border=0 cellpadding=0 cellspacing=0 width=1047 class=xl6820196
 style='border-collapse:collapse;table-layout:fixed;width:785pt'>
 <col class=xl6820196 width=12 span=6 style='width:9pt'>
 <col class=xl6820196 width=7 style='mso-width-source:userset;mso-width-alt:
 256;width:5pt'>
 <col class=xl6820196 width=8 style='mso-width-source:userset;mso-width-alt:
 284;width:6pt'>
 <col class=xl6820196 width=12 span=80 style='width:9pt'>
 <tr height=14 style='height:10.8pt'>
  <td colspan=88 height=14 class=xl9320196 width=1047 style='height:10.8pt;
  width:785pt'>Приложение N 1 к письму ФНС России от
  21.10.2013 N ММВ-20-3/96@</td>
 </tr>
 <tr height=16 style='height:12.0pt'>
  <td colspan=8 rowspan=4 height=52 class=xl9820196 width=87 style='height:
  40.5pt;width:65pt'>Универсальный
  передаточный документ</td>
  <td colspan=9 class=xl9420196>Счет-фактура N<span
  style='mso-spacerun:yes'> </span></td>
  <td colspan=10 class=xl9620196>{{$doc->doc_num}}</td>
  <td colspan=2 class=xl9120196>от</td>
  <td colspan=10 class=xl9620196>{{$doc->doc_date}}</td>
  <td colspan=4 class=xl6820196>(1)</td>
  <td colspan=45 rowspan=3 class=xl9220196 width=540 style='width:405pt'>Приложение
  N 1 к постановлению Правительства Российской Федерации от 26 декабря 2011
  года № 1137<br>
    <span style='mso-spacerun:yes'> </span>(в редакции пост. Правительства РФ
  от 19.08.2017 № 981) (с учетом Письма ФНС России от 17.06.2021 N ЗГ-3-3/4368)</td>
 </tr>
 <tr height=16 style='height:12.0pt'>
  <td colspan=9 height=16 class=xl9420196 style='height:12.0pt'>Исправление N</td>
  <td colspan=10 class=xl9720196>&nbsp;</td>
  <td colspan=2 class=xl9120196>от</td>
  <td colspan=10 class=xl9720196>&nbsp;</td>
  <td colspan=4 class=xl6820196>(1а)</td>
 </tr>
 <tr height=6 style='mso-height-source:userset;height:5.25pt'>
  <td colspan=35 height=6 class=xl6920196 style='height:5.25pt'>&nbsp;</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=19 height=14 class=xl10020196 style='height:11.25pt'>Продавец</td>
  <td colspan=58 class=xl7620196>{{$doc->saler}}</td>
  <td colspan=3 class=xl7020196>(2)</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=4 height=14 class=xl6820196 style='height:11.25pt'>Статус:<span
  style='mso-spacerun:yes'> </span></td>
  <td colspan=2 class=xl10320196 style='border-right:1.0pt solid black'>{{$doc->status}}</td>
  <td colspan=2 class=xl6920196 style='border-right:1.0pt solid black;
  border-left:none'>&nbsp;</td>
  <td colspan=19 class=xl7720196 style='border-left:none'>Адрес</td>
  <td colspan=58 class=xl9020196>{{$doc->saler_addr}}</td>
  <td colspan=3 class=xl7020196>(2а)</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=8 rowspan=10 height=140 class=xl13120196 width=87
  style='border-right:1.0pt solid black;height:112.5pt;width:65pt'><br>
    1 - счет-фактура и передаточный документ (акт) <br>
    2 - передаточный документ (акт)</td>
  <td colspan=19 class=xl7720196 style='border-left:none'>ИНН/КПП продавца</td>
  <td colspan=58 class=xl9020196>{{$doc->saler_inn_kpp}}</td>
  <td colspan=3 class=xl7020196>(2б)</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=19 height=14 class=xl7720196 style='height:11.25pt;border-left:
  none'>Грузоотправитель и его адрес</td>
  <td colspan=58 class=xl9020196>{{$doc->saler_go_addr}}</td>
  <td colspan=3 class=xl7020196>(3)</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=19 height=14 class=xl7720196 style='height:11.25pt;border-left:
  none'>Грузополучатель и его адрес</td>
  <td colspan=58 class=xl9020196>{{$doc->buyer_gp_addr}}</td>
  <td colspan=3 class=xl7020196>(4)</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=19 height=14 class=xl7720196 style='height:11.25pt;border-left:
  none'>К платежно-расчетному документу</td>
  <td colspan=2 class=xl13020196>N<span style='mso-spacerun:yes'> </span></td>
  <td colspan=8 class=xl8920196>{{$doc->buyer_pp_num}}</td>
  <td colspan=2 class=xl7020196>от</td>
  <td colspan=46 class=xl9020196>{{$doc->buyer_pp_date}}</td>
  <td colspan=3 class=xl7020196>(5)</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td height=14 class=xl7720196 style='height:11.25pt;border-left:none'>&nbsp;</td>
  <td colspan=18 class=xl13320196>Документ об отгрузке № п/п</td>
  <td colspan=16 class=xl13420196>&nbsp;</td>
  <td colspan=3 class=xl13520196>№</td>
  <td colspan=15 class=xl13620196>&nbsp;</td>
  <td colspan=2 class=xl7020196>от</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td class=xl7620196>&nbsp;</td>
  <td colspan=3 class=xl7020196>(5а)</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=19 height=14 class=xl10020196 style='height:11.25pt;border-left:
  none'>Покупатель</td>
  <td colspan=58 class=xl7620196>{{$doc->buyer}}</td>
  <td colspan=3 class=xl7020196>(6)</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=19 height=14 class=xl7720196 style='height:11.25pt;border-left:
  none'>Адрес</td>
  <td colspan=58 class=xl9020196>{{$doc->buyer_addr}}</td>
  <td colspan=3 class=xl7020196>(6а)</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=19 height=14 class=xl7720196 style='height:11.25pt;border-left:
  none'>ИНН/КПП покупателя</td>
  <td colspan=58 class=xl9020196>{{$doc->buyer_inn_kpp}}</td>
  <td colspan=3 class=xl7020196>(6б)</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=19 height=14 class=xl7720196 style='height:11.25pt;border-left:
  none'>Валюта: наименование, код</td>
  <td colspan=58 class=xl8220196>{{$doc->valuta}}</td>
  <td colspan=3 class=xl7020196>(7)</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td height=14 class=xl7520196 width=12 style='height:11.25pt;border-left:
  none;width:9pt'>&nbsp;</td>
  <td colspan=31 class=xl8820196 width=372 style='width:279pt'>Идентификатор
  государственного контракта, договора (соглашения)</td>
  <td colspan=45 class=xl8020196 width=540 style='width:405pt'>&nbsp;</td>
  <td colspan=3 class=xl7020196>(8)</td>
 </tr>
 <tr height=6 style='mso-height-source:userset;height:4.5pt'>
  <td colspan=8 height=6 class=xl6820196 style='border-right:1.0pt solid black;
  height:4.5pt'></td>
  <td colspan=80 class=xl6920196 style='border-left:none'>&nbsp;</td>
 </tr>
 <tr height=29 style='mso-height-source:userset;height:21.75pt'>
  <td colspan=2 rowspan=2 height=91 class=xl10520196 width=24 style='border-right:
  .5pt solid black;border-bottom:.5pt solid black;height:68.25pt;width:18pt'>N
  п/п</td>
  <td colspan=6 rowspan=2 class=xl10520196 width=63 style='border-bottom:.5pt solid black;
  width:47pt'>Код товара/ работ, услуг</td>
  <td colspan=14 rowspan=2 class=xl8620196 width=168 style='width:126pt'>Наименование
  товара (описание выполненных работ, оказанных услуг), имущественного права</td>
  <td colspan=3 rowspan=2 class=xl10520196 width=36 style='border-right:.5pt solid black;
  border-bottom:.5pt solid black;width:27pt'>Код вида товара</td>
  <td colspan=9 class=xl8120196 width=108 style='border-left:none;width:81pt'>Единица
  измерения</td>
  <td colspan=4 rowspan=2 class=xl10520196 width=48 style='border-right:.5pt solid black;
  border-bottom:.5pt solid black;width:36pt'>Коли-<br>
    чество (объем)</td>
  <td colspan=5 rowspan=2 class=xl10520196 width=60 style='border-right:.5pt solid black;
  border-bottom:.5pt solid black;width:45pt'>Цена (тариф) за единицу измерения</td>
  <td colspan=7 rowspan=2 class=xl10520196 width=84 style='border-right:.5pt solid black;
  border-bottom:.5pt solid black;width:63pt'>Стоимость товаров <br>
    (работ, услуг), имущественных прав без налога - всего</td>
  <td colspan=4 rowspan=2 class=xl10520196 width=48 style='border-right:.5pt solid black;
  border-bottom:.5pt solid black;width:36pt'>В том числе сумма акциза</td>
  <td colspan=4 rowspan=2 class=xl10520196 width=48 style='border-right:.5pt solid black;
  border-bottom:.5pt solid black;width:36pt'>Нало-<br>
    говая ставка</td>
  <td colspan=6 rowspan=2 class=xl10520196 width=72 style='border-right:.5pt solid black;
  border-bottom:.5pt solid black;width:54pt'>Сумма налога, предъявляемая
  покупателю</td>
  <td colspan=7 rowspan=2 class=xl10520196 width=84 style='border-right:.5pt solid black;
  border-bottom:.5pt solid black;width:63pt'>Стоимость товаров <br>
    (работ, услуг), имущественных прав с налогом - всего</td>
  <td colspan=11 class=xl8320196 width=132 style='border-right:.5pt solid black;
  border-left:none;width:99pt'>Страна происхождения товара</td>
  <td colspan=6 rowspan=2 class=xl10520196 width=72 style='border-right:.5pt solid black;
  border-bottom:.5pt solid black;width:54pt'>Регистра-ционный номер таможенной
  декларации</td>
 </tr>
 <tr height=62 style='mso-height-source:userset;height:46.5pt'>
  <td colspan=2 height=62 class=xl8120196 width=24 style='height:46.5pt;
  border-left:none;width:18pt'>код</td>
  <td colspan=7 class=xl8120196 width=84 style='border-left:none;width:63pt'>условное
  обозначение (национальное)</td>
  <td colspan=4 class=xl8120196 width=48 style='border-left:none;width:36pt'>Цифро-<br>
    вой код</td>
  <td colspan=7 class=xl8120196 width=84 style='border-left:none;width:63pt'>Краткое
  наименование</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=2 height=14 class=xl8320196 width=24 style='border-right:.5pt solid black;
  height:11.25pt;width:18pt'>А</td>
  <td colspan=6 class=xl8320196 width=63 style='border-left:none;width:47pt'>Б</td>
  <td colspan=14 class=xl8620196 width=168 style='width:126pt'>1</td>
  <td colspan=3 class=xl8320196 width=36 style='border-right:.5pt solid black;
  border-left:none;width:27pt'>1а</td>
  <td colspan=2 class=xl8120196 width=24 style='border-left:none;width:18pt'>2</td>
  <td colspan=7 class=xl8120196 width=84 style='border-left:none;width:63pt'>2а</td>
  <td colspan=4 class=xl8120196 width=48 style='border-left:none;width:36pt'>3</td>
  <td colspan=5 class=xl8120196 width=60 style='border-left:none;width:45pt'>4</td>
  <td colspan=7 class=xl8120196 width=84 style='border-left:none;width:63pt'>5</td>
  <td colspan=4 class=xl8120196 width=48 style='border-left:none;width:36pt'>6</td>
  <td colspan=4 class=xl8120196 width=48 style='border-left:none;width:36pt'>7</td>
  <td colspan=6 class=xl8120196 width=72 style='border-left:none;width:54pt'>8</td>
  <td colspan=7 class=xl8120196 width=84 style='border-left:none;width:63pt'>9</td>
  <td colspan=4 class=xl8120196 width=48 style='border-left:none;width:36pt'>10</td>
  <td colspan=7 class=xl8120196 width=84 style='border-left:none;width:63pt'>10а</td>
  <td colspan=6 class=xl8120196 width=72 style='border-left:none;width:54pt'>11</td>
 </tr>
 @if (isset($doc_table))
    @foreach ($doc_table as $row)
    @if ($loop->first)
        <tr height=16 style='mso-height-source:userset;height:12.0pt'>
        <td colspan=2 height=16 class=xl8320196 width=24 style='border-right:.5pt solid black;
        height:12.0pt;width:18pt'>{{$row->npp}}</td>
        <td colspan=6 class=xl8320196 width=63 style='border-left:none;width:47pt'>{{$row->code}}</td>
        <td colspan=14 class=xl8620196 width=168 style='width:126pt'>{{$row->name}}</td>
        <td colspan=3 class=xl8320196 width=36 style='border-right:.5pt solid black;
        border-left:none;width:27pt'>{{$row->code_tov}}</td>
        <td colspan=2 class=xl7920196 style='border-left:none'>{{$row->ed_ism_code}}</td>
        <td colspan=7 class=xl7920196 style='border-left:none'>{{$row->ed_ism}}</td>
        <td colspan=4 class=xl8720196 style='border-left:none'>{{$row->kolvo}}</td>
        <td colspan=5 class=xl7820196 style='border-left:none'>{{$row->price}}</td>
        <td colspan=7 class=xl7820196 style='border-left:none'>{{$row->summa}}</td>
        <td colspan=4 class=xl7820196 style='border-left:none'>&nbsp;</td>
        <td colspan=4 class=xl7820196 style='border-left:none'>{{$row->stavka_nds}}</td>
        <td colspan=6 class=xl7820196 style='border-left:none'>{{$row->sum_nds}}</td>
        <td colspan=7 class=xl7820196 style='border-left:none'>{{$row->sum}}</td>
        <td colspan=4 class=xl7920196 style='border-left:none'>&nbsp;</td>
        <td colspan=7 class=xl7920196 style='border-left:none'>&nbsp;</td>
        <td colspan=6 class=xl7920196 style='border-left:none'>&nbsp;</td>
        </tr>
    @elseif ($loop->last)
        <tr height=16 style='mso-height-source:userset;height:12.0pt'>
        <td colspan=2 height=16 class=xl8320196 width=24 style='border-right:.5pt solid black;
        height:12.0pt;width:18pt'>{{$row->npp}}</td>
        <td colspan=6 class=xl8320196 width=63 style='border-left:none;width:47pt'>{{$row->code}}</td>
        <td colspan=14 class=xl8620196 width=168 style='width:126pt'>{{$row->name}}</td>
        <td colspan=3 class=xl8320196 width=36 style='border-right:.5pt solid black;
        border-left:none;width:27pt'>{{$row->code_tov}}</td>
        <td colspan=2 class=xl7920196 style='border-left:none'>{{$row->ed_ism_code}}</td>
        <td colspan=7 class=xl7920196 style='border-left:none'>{{$row->ed_ism}}</td>
        <td colspan=4 class=xl8720196 style='border-left:none'>{{$row->kolvo}}</td>
        <td colspan=5 class=xl7820196 style='border-left:none'>{{$row->price}}</td>
        <td colspan=7 class=xl7820196 style='border-left:none'>{{$row->summa}}</td>
        <td colspan=4 class=xl7820196 style='border-left:none'>&nbsp;</td>
        <td colspan=4 class=xl7820196 style='border-left:none'>{{$row->stavka_nds}}</td>
        <td colspan=6 class=xl7820196 style='border-left:none'>{{$row->sum_nds}}</td>
        <td colspan=7 class=xl7820196 style='border-left:none'>{{$row->sum}}</td>
        <td colspan=4 class=xl7920196 style='border-left:none'>&nbsp;</td>
        <td colspan=7 class=xl7920196 style='border-left:none'>&nbsp;</td>
        <td colspan=6 class=xl7920196 style='border-left:none'>&nbsp;</td>
        </tr>

    @else
        <tr height=16 style='mso-height-source:userset;height:12.0pt'>
        <td colspan=2 height=16 class=xl8320196 width=24 style='border-right:.5pt solid black;
        height:12.0pt;width:18pt'>{{$row->npp}}</td>
        <td colspan=6 class=xl8320196 width=63 style='border-left:none;width:47pt'>{{$row->code}}</td>
        <td colspan=14 class=xl8620196 width=168 style='width:126pt'>{{$row->name}}</td>
        <td colspan=3 class=xl8320196 width=36 style='border-right:.5pt solid black;
        border-left:none;width:27pt'>{{$row->code_tov}}</td>
        <td colspan=2 class=xl7920196 style='border-left:none'>{{$row->ed_ism_code}}</td>
        <td colspan=7 class=xl7920196 style='border-left:none'>{{$row->ed_ism}}</td>
        <td colspan=4 class=xl8720196 style='border-left:none'>{{$row->kolvo}}</td>
        <td colspan=5 class=xl7820196 style='border-left:none'>{{$row->price}}</td>
        <td colspan=7 class=xl7820196 style='border-left:none'>{{$row->summa}}</td>
        <td colspan=4 class=xl7820196 style='border-left:none'>&nbsp;</td>
        <td colspan=4 class=xl7820196 style='border-left:none'>{{$row->stavka_nds}}</td>
        <td colspan=6 class=xl7820196 style='border-left:none'>{{$row->sum_nds}}</td>
        <td colspan=7 class=xl7820196 style='border-left:none'>{{$row->sum}}</td>
        <td colspan=4 class=xl7920196 style='border-left:none'>&nbsp;</td>
        <td colspan=7 class=xl7920196 style='border-left:none'>&nbsp;</td>
        <td colspan=6 class=xl7920196 style='border-left:none'>&nbsp;</td>
        </tr>
    @endif
    @endforeach
    @endif
 <tr height=16 style='mso-height-source:userset;height:12.0pt'>
  <td colspan=2 height=16 class=xl8320196 width=24 style='border-right:.5pt solid black;
  height:12.0pt;width:18pt'>&nbsp;</td>
  <td colspan=6 class=xl8320196 width=63 style='border-left:none;width:47pt'>&nbsp;</td>
  <td colspan=35 class=xl11520196 width=420 style='border-right:.5pt solid black;
  width:315pt'>Всего к оплате</td>
  <td colspan=7 class=xl7820196 style='border-left:none'>{{$itogs->summa}}</td>
  <td colspan=8 class=xl11820196 style='border-right:.5pt solid black;
  border-left:none'>Х</td>
  <td colspan=6 class=xl7820196 style='border-left:none'>{{$itogs->sum_nds}}</td>
  <td colspan=7 class=xl7820196 style='border-left:none'>{{$itogs->sum_sum}}</td>
  <td colspan=17 class=xl12120196></td>
 </tr>
 <tr height=6 style='mso-height-source:userset;height:4.5pt'>
  <td colspan=8 height=6 class=xl11320196 style='height:4.5pt'>&nbsp;</td>
  <td colspan=80 class=xl11420196>&nbsp;</td>
 </tr>
 <tr height=30 style='mso-height-source:userset;height:22.5pt'>
  <td colspan=8 height=30 class=xl8820196 width=87 style='height:22.5pt;
  width:65pt'>Документ составлен на<span style='mso-spacerun:yes'> </span></td>
  <td class=xl6920196>&nbsp;</td>
  <td colspan=19 class=xl8820196 width=228 style='width:171pt'>Руководитель
  организации <br>
    или иное уполномоченное лицо</td>
  <td colspan=6 class=xl11220196 width=72 style='width:54pt'>&nbsp;</td>
  <td class=xl7120196 width=12 style='width:9pt'></td>
  <td colspan=15 class=xl11220196 width=180 style='width:135pt'>{{$doc->podpis_ceo}}</td>
  <td colspan=16 class=xl8820196 width=192 style='width:144pt'>Главный
  бухгалтер <br>
    или иное уполномоченное лицо</td>
  <td colspan=6 class=xl11220196 width=72 style='width:54pt'>&nbsp;</td>
  <td class=xl7120196 width=12 style='width:9pt'></td>
  <td colspan=15 class=xl11220196 width=180 style='width:135pt'>{{$doc->podpis_account}}</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=3 height=14 class=xl8920196 style='height:11.25pt'>{{$doc->pages_count}}</td>
  <td colspan=5 class=xl6820196><span style='mso-spacerun:yes'> </span>листах</td>
  <td class=xl6920196>&nbsp;</td>
  <td colspan=19 class=xl8820196 width=228 style='width:171pt'></td>
  <td colspan=6 class=xl6520196 width=72 style='width:54pt'>(подпись)</td>
  <td class=xl6520196 width=12 style='width:9pt'></td>
  <td colspan=15 class=xl6520196 width=180 style='width:135pt'>(ф.и.о.)</td>
  <td colspan=16 class=xl10920196 width=192 style='width:144pt'></td>
  <td colspan=6 class=xl6520196 width=72 style='width:54pt'>(подпись)</td>
  <td class=xl6520196 width=12 style='width:9pt'></td>
  <td colspan=15 class=xl6520196 width=180 style='width:135pt'>(ф.и.о.)</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=8 height=14 class=xl6820196 style='border-right:1.0pt solid black;
  height:11.25pt'></td>
  <td class=xl6920196 style='border-left:none'>&nbsp;</td>
  <td colspan=19 class=xl8820196 width=228 style='width:171pt'>Индивидуальный
  предприниматель</td>
  <td colspan=6 class=xl11220196 width=72 style='width:54pt'>&nbsp;</td>
  <td class=xl7120196 width=12 style='width:9pt'></td>
  <td colspan=15 class=xl11220196 width=180 style='width:135pt'>{{$doc->ip_fio}}</td>
  <td colspan=3 class=xl8820196 width=36 style='width:27pt'></td>
  <td colspan=35 class=xl11220196 width=420 style='width:315pt'>{{$doc->ip_ogrnip}}</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=8 height=14 class=xl6820196 style='border-right:1.0pt solid black;
  height:11.25pt'></td>
  <td class=xl7220196 style='border-left:none'>&nbsp;</td>
  <td colspan=19 class=xl12420196 width=228 style='width:171pt'>или иное
  уполномоченное лицо</td>
  <td colspan=6 class=xl6620196 width=72 style='width:54pt'>(подпись)</td>
  <td class=xl6620196 width=12 style='width:9pt'>&nbsp;</td>
  <td colspan=15 class=xl6620196 width=180 style='width:135pt'>(ф.и.о.)</td>
  <td colspan=3 class=xl12220196 width=36 style='width:27pt'>&nbsp;</td>
  <td colspan=35 class=xl12320196 width=420 style='width:315pt'>(реквизиты
  свидетельства о государственной регистрации индивидуального предпринимателя)</td>
 </tr>
 <tr height=6 style='mso-height-source:userset;height:4.5pt'>
  <td colspan=88 height=6 class=xl6820196 style='height:4.5pt'></td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=22 height=14 class=xl6820196 style='height:11.25pt'>Основание
  передачи (сдачи) / получения (приемки)</td>
  <td class=xl6820196></td>
  <td class=xl6820196></td>
  <td class=xl6820196></td>
  <td colspan=60 class=xl11220196 width=720 style='width:540pt'>{{$doc->doverennost}}</td>
  <td colspan=3 class=xl7120196 width=36 style='width:27pt'>[9]</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=22 height=14 class=xl6820196 style='height:11.25pt'></td>
  <td class=xl6820196></td>
  <td class=xl6820196></td>
  <td class=xl6820196></td>
  <td colspan=60 class=xl12520196 width=720 style='width:540pt'>(договор;
  доверенность и др.)</td>
  <td colspan=3 class=xl7120196 width=36 style='width:27pt'></td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=16 height=14 class=xl6820196 style='height:11.25pt'>Данные о
  транспортировке и грузе</td>
  <td colspan=69 class=xl11220196 width=828 style='width:621pt'>&nbsp;</td>
  <td colspan=3 class=xl7120196 width=36 style='width:27pt'>[10]</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=16 height=14 class=xl6820196 style='height:11.25pt'></td>
  <td colspan=69 class=xl12520196 width=828 style='width:621pt'>(транспортная
  накладная, поручение экспедитору, экспедиторская / складская расписка и др. /
  масса нетто/ брутто груза, если не приведены ссылки на транспортные
  документы, содержащие эти сведения)</td>
  <td colspan=3 class=xl7120196 width=36 style='width:27pt'></td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=45 height=14 class=xl6820196 style='border-right:1.0pt solid black;
  height:11.25pt'>Товар (груз) передал / услуги, результаты работ, права сдал</td>
  <td class=xl6920196 style='border-left:none'>&nbsp;</td>
  <td colspan=42 class=xl8820196 width=504 style='width:378pt'>Товар (груз)
  получил / услуги, результаты работ, права принял<span
  style='mso-spacerun:yes'> </span></td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=13 height=14 class=xl8920196 style='height:11.25pt'>{{$doc->keeper_firm_position}}</td>
  <td class=xl7020196></td>
  <td colspan=13 class=xl8920196>&nbsp;</td>
  <td class=xl7020196></td>
  <td colspan=14 class=xl8920196>{{$doc->keeper_fio}}</td>
  <td colspan=3 class=xl7020196 style='border-right:1.0pt solid black'>[11]</td>
  <td class=xl6920196 style='border-left:none'>&nbsp;</td>
  <td colspan=13 class=xl8920196>&nbsp;</td>
  <td class=xl7020196></td>
  <td colspan=10 class=xl8920196>&nbsp;</td>
  <td class=xl7020196></td>
  <td colspan=14 class=xl8920196>&nbsp;</td>
  <td colspan=3 class=xl7120196 width=36 style='width:27pt'>[16]</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=13 height=14 class=xl6720196 style='height:11.25pt'>(должность)</td>
  <td class=xl6720196></td>
  <td colspan=13 class=xl6720196>(подпись)</td>
  <td class=xl6720196></td>
  <td colspan=14 class=xl6720196>(ф.и.о.)</td>
  <td colspan=3 class=xl12720196 style='border-right:1.0pt solid black'></td>
  <td class=xl7420196 style='border-left:none'>&nbsp;</td>
  <td colspan=13 class=xl6720196>(должность)</td>
  <td class=xl6720196></td>
  <td colspan=10 class=xl6720196>(подпись)</td>
  <td class=xl6720196></td>
  <td colspan=14 class=xl6720196>(ф.и.о.)</td>
  <td colspan=3 class=xl7120196 width=36 style='width:27pt'></td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=15 height=14 class=xl6820196 style='height:11.25pt'>Дата
  отгрузки, передачи (сдачи)</td>
  <td class=xl7320196>&quot;</td>
  <td colspan=2 class=xl8920196>{{$doc->out_date_date}}</td>
  <td class=xl6820196>&quot;</td>
  <td colspan=9 class=xl8920196>{{$doc->out_date_month}}</td>
  <td colspan=2 class=xl7320196>20</td>
  <td colspan=2 class=xl7620196>{{$doc->out_date_year}}</td>
  <td colspan=10 class=xl6820196>г.</td>
  <td colspan=3 class=xl7020196 style='border-right:1.0pt solid black'>[12]</td>
  <td class=xl6920196 style='border-left:none'>&nbsp;</td>
  <td colspan=13 class=xl6820196>Дата получения (приемки)</td>
  <td class=xl7320196>&quot;</td>
  <td colspan=2 class=xl8920196>&nbsp;</td>
  <td class=xl6820196>&quot;</td>
  <td colspan=6 class=xl8920196>&nbsp;</td>
  <td colspan=2 class=xl7320196>20</td>
  <td colspan=2 class=xl7620196>&nbsp;</td>
  <td colspan=12 class=xl6820196>г.</td>
  <td colspan=3 class=xl7120196 width=36 style='width:27pt'>[17]</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=42 height=14 class=xl6820196 style='height:11.25pt'>Иные сведения
  об отгрузке, передаче</td>
  <td colspan=3 class=xl7020196 style='border-right:1.0pt solid black'></td>
  <td class=xl6920196 style='border-left:none'>&nbsp;</td>
  <td colspan=39 class=xl6820196>Иные сведения о получении, приемке</td>
  <td colspan=3 class=xl7120196 width=36 style='width:27pt'></td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=42 height=14 class=xl8920196 style='height:11.25pt'>&nbsp;</td>
  <td colspan=3 class=xl7020196 style='border-right:1.0pt solid black'>[13]</td>
  <td class=xl6920196 style='border-left:none'>&nbsp;</td>
  <td colspan=39 class=xl8920196>&nbsp;</td>
  <td colspan=3 class=xl7120196 width=36 style='width:27pt'>[18]</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=42 height=14 class=xl12920196 style='height:11.25pt'>(ссылки на
  неотъемлемые приложения, сопутствующие документы, иные документы и т.п.)</td>
  <td colspan=3 class=xl12720196 style='border-right:1.0pt solid black'></td>
  <td class=xl7420196 style='border-left:none'>&nbsp;</td>
  <td colspan=39 class=xl12920196>(информация о наличии/отсутствии претензии;
  ссылки на неотъемлемые приложения, и другие<span style='mso-spacerun:yes'> 
  </span>документы и т.п.)</td>
  <td colspan=3 class=xl7120196 width=36 style='width:27pt'></td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=42 height=14 class=xl6820196 style='height:11.25pt'>Ответственный
  за правильность оформления факта хозяйственной жизни</td>
  <td colspan=3 class=xl7020196 style='border-right:1.0pt solid black'></td>
  <td class=xl6920196 style='border-left:none'>&nbsp;</td>
  <td colspan=39 class=xl6820196>Ответственный за правильность оформления факта
  хозяйственной жизни</td>
  <td colspan=3 class=xl7120196 width=36 style='width:27pt'></td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=13 height=14 class=xl8920196 style='height:11.25pt'>{{$doc->manager_firm_position}}</td>
  <td class=xl7020196></td>
  <td colspan=13 class=xl8920196>&nbsp;</td>
  <td class=xl7020196></td>
  <td colspan=14 class=xl8920196>{{$doc->manager_fio}}</td>
  <td colspan=3 class=xl7020196 style='border-right:1.0pt solid black'>[14]</td>
  <td class=xl6920196 style='border-left:none'>&nbsp;</td>
  <td colspan=13 class=xl8920196>&nbsp;</td>
  <td class=xl7020196></td>
  <td colspan=10 class=xl8920196>&nbsp;</td>
  <td class=xl7020196></td>
  <td colspan=14 class=xl8920196>&nbsp;</td>
  <td colspan=3 class=xl7120196 width=36 style='width:27pt'>[19]</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=13 height=14 class=xl6720196 style='height:11.25pt'>(должность)</td>
  <td class=xl6720196></td>
  <td colspan=13 class=xl6720196>(подпись)</td>
  <td class=xl6720196></td>
  <td colspan=14 class=xl6720196>(ф.и.о.)</td>
  <td colspan=3 class=xl12720196 style='border-right:1.0pt solid black'></td>
  <td class=xl7420196 style='border-left:none'>&nbsp;</td>
  <td colspan=13 class=xl6720196>(должность)</td>
  <td class=xl6720196></td>
  <td colspan=10 class=xl6720196>(подпись)</td>
  <td class=xl6720196></td>
  <td colspan=14 class=xl6720196>(ф.и.о.)</td>
  <td colspan=3 class=xl7020196></td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=42 height=14 class=xl6820196 style='height:11.25pt'>Наименование
  экономического субъекта – составителя документа (в т.ч. комиссионера /
  агента)</td>
  <td colspan=3 class=xl7020196 style='border-right:1.0pt solid black'></td>
  <td class=xl6920196 style='border-left:none'>&nbsp;</td>
  <td colspan=39 class=xl6820196>Наименование экономического субъекта -
  составителя документа</td>
  <td colspan=3 class=xl7020196></td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=42 height=14 class=xl7620196 style='height:11.25pt'>&nbsp;</td>
  <td colspan=3 class=xl7020196 style='border-right:1.0pt solid black'>[15]</td>
  <td class=xl6920196 style='border-left:none'>&nbsp;</td>
  <td colspan=39 class=xl7620196>&nbsp;</td>
  <td colspan=3 class=xl7020196>[20]</td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=42 height=14 class=xl12920196 style='height:11.25pt'>(может не
  заполняться при проставлении печати в М.П., может быть указан ИНН / КПП)</td>
  <td colspan=3 class=xl12720196 style='border-right:1.0pt solid black'></td>
  <td class=xl7420196 style='border-left:none'>&nbsp;</td>
  <td colspan=39 class=xl12920196>(может не заполняться при проставлении печати
  в М.П., может быть указан ИНН / КПП)</td>
  <td colspan=3 class=xl7020196></td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:11.25pt'>
  <td colspan=13 height=14 class=xl7020196 style='height:11.25pt'>М.П.</td>
  <td colspan=29 class=xl6820196></td>
  <td colspan=3 class=xl7020196 style='border-right:1.0pt solid black'></td>
  <td class=xl6920196 style='border-left:none'>&nbsp;</td>
  <td colspan=13 class=xl7020196>М.П.</td>
  <td colspan=26 class=xl6820196></td>
  <td colspan=3 class=xl7020196></td>
 </tr>
</table>

</div>

@endsection


