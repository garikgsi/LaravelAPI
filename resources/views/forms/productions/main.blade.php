@extends('forms.skeleton')

@section('title')
    Форма производства
@endsection

@section('style')
<style id="акт списания в производство_31808_Styles">
<!--table
	{mso-displayed-decimal-separator:"\,";
	mso-displayed-thousand-separator:" ";}
.xl6531808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl6631808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:#444444;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Tahoma, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:bottom;
	border-top:1.0pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:1.0pt solid windowtext;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl6731808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:#444444;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Tahoma, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl6831808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl6931808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:9.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7031808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:8.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:bottom;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7131808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:bottom;
	border-top:.5pt solid windowtext;
	border-right:none;
	border-bottom:.5pt solid windowtext;
	border-left:none;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7231808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border-top:1.0pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7331808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border-top:1.0pt solid windowtext;
	border-right:1.0pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7431808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7531808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border-top:.5pt solid windowtext;
	border-right:1.0pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7631808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:1.0pt solid windowtext;
	border-left:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7731808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border-top:.5pt solid windowtext;
	border-right:1.0pt solid windowtext;
	border-bottom:1.0pt solid windowtext;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7831808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:5.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:top;
	border-top:.5pt solid windowtext;
	border-right:none;
	border-bottom:none;
	border-left:none;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7931808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:5.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:top;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl8031808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:left;
	vertical-align:bottom;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl8131808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:bottom;
	border-top:none;
	border-right:none;
	border-bottom:.5pt solid windowtext;
	border-left:none;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl8231808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl8331808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border-top:1.0pt solid windowtext;
	border-right:none;
	border-bottom:1.0pt solid windowtext;
	border-left:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl8431808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border-top:1.0pt solid windowtext;
	border-right:1.0pt solid windowtext;
	border-bottom:1.0pt solid windowtext;
	border-left:none;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl8531808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:left;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl8631808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:left;
	vertical-align:bottom;
	border-top:.5pt solid windowtext;
	border-right:none;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl8731808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl8831808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:1.0pt solid windowtext;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl8931808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl9031808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border-top:.5pt solid windowtext;
	border-right:1.0pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl9131808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:right;
	vertical-align:bottom;
	border-top:1.0pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:.5pt solid windowtext;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl9231808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:bottom;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl9331808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:bottom;
	border-top:none;
	border-right:none;
	border-bottom:.5pt solid windowtext;
	border-left:none;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl9431808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:5.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:top;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl9531808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:middle;
	border-top:.5pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:none;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl9631808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:bottom;
	border-top:1.0pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:1.0pt solid windowtext;
	border-left:1.0pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl9731808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:bottom;
	border-top:1.0pt solid windowtext;
	border-right:.5pt solid windowtext;
	border-bottom:1.0pt solid windowtext;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl9831808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:11.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:bottom;
	border-top:1.0pt solid windowtext;
	border-right:1.0pt solid windowtext;
	border-bottom:1.0pt solid windowtext;
	border-left:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl9931808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:12.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:bottom;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl10031808
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:12.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Calibri, sans-serif;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:center;
	vertical-align:top;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
-->
    </style>
@endsection

@section('body')
<div id="акт списания в производство_31808" align=center>

<table border=0 cellpadding=0 cellspacing=0 width=625 class=xl6531808
 style='border-collapse:collapse;table-layout:fixed;width:471pt'>
 <col class=xl6531808 width=6 style='mso-width-source:userset;mso-width-alt:
 199;width:4pt'>
 <col class=xl6531808 width=38 style='mso-width-source:userset;mso-width-alt:
 1365;width:29pt'>
 <col class=xl6531808 width=26 style='mso-width-source:userset;mso-width-alt:
 938;width:20pt'>
 <col class=xl6531808 width=50 style='mso-width-source:userset;mso-width-alt:
 1763;width:37pt'>
 <col class=xl6531808 width=34 style='mso-width-source:userset;mso-width-alt:
 1223;width:26pt'>
 <col class=xl6531808 width=32 style='mso-width-source:userset;mso-width-alt:
 1137;width:24pt'>
 <col class=xl6531808 width=40 style='mso-width-source:userset;mso-width-alt:
 1422;width:30pt'>
 <col class=xl6531808 width=30 style='mso-width-source:userset;mso-width-alt:
 1052;width:22pt'>
 <col class=xl6531808 width=28 style='mso-width-source:userset;mso-width-alt:
 995;width:21pt'>
 <col class=xl6531808 width=57 style='mso-width-source:userset;mso-width-alt:
 2019;width:43pt'>
 <col class=xl6531808 width=37 style='mso-width-source:userset;mso-width-alt:
 1308;width:28pt'>
 <col class=xl6531808 width=41 style='mso-width-source:userset;mso-width-alt:
 1450;width:31pt'>
 <col class=xl6531808 width=26 style='mso-width-source:userset;mso-width-alt:
 938;width:20pt'>
 <col class=xl6531808 width=26 style='mso-width-source:userset;mso-width-alt:
 910;width:19pt'>
 <col class=xl6531808 width=25 style='mso-width-source:userset;mso-width-alt:
 881;width:19pt'>
 <col class=xl6531808 width=36 style='mso-width-source:userset;mso-width-alt:
 1280;width:27pt'>
 <col class=xl6531808 width=42 style='mso-width-source:userset;mso-width-alt:
 1507;width:32pt'>
 <col class=xl6531808 width=46 style='mso-width-source:userset;mso-width-alt:
 1649;width:35pt'>
 <col class=xl6531808 width=5 style='mso-width-source:userset;mso-width-alt:
 170;width:4pt'>
 <tr height=6 style='mso-height-source:userset;height:4.8pt'>
  <td height=6 class=xl6531808 width=6 style='height:4.8pt;width:4pt'></td>
  <td class=xl6531808 width=38 style='width:29pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=50 style='width:37pt'></td>
  <td class=xl6531808 width=34 style='width:26pt'></td>
  <td class=xl6531808 width=32 style='width:24pt'></td>
  <td class=xl6531808 width=40 style='width:30pt'></td>
  <td class=xl6531808 width=30 style='width:22pt'></td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td class=xl6531808 width=57 style='width:43pt'></td>
  <td class=xl6531808 width=37 style='width:28pt'></td>
  <td class=xl6531808 width=41 style='width:31pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=26 style='width:19pt'></td>
  <td class=xl6531808 width=25 style='width:19pt'></td>
  <td class=xl6531808 width=36 style='width:27pt'></td>
  <td class=xl6531808 width=42 style='width:32pt'></td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='mso-height-source:userset;height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td colspan=6 class=xl9331808 width=220 style='width:166pt'>{{ isset($doc->firm) ? $doc->firm : 'ООО "Концерн "МОЙДОДЫР"' }}  </td>
  <td class=xl6531808 width=30 style='width:22pt'></td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td class=xl6531808 width=57 style='width:43pt'></td>
  <td class=xl6531808 width=37 style='width:28pt'></td>
  <td class=xl6531808 width=41 style='width:31pt'></td>
  <td colspan=6 rowspan=3 class=xl9231808 width=201 style='width:152pt'>Форма
  утверждена<br>
    Учетной политикой от 09.01.2013</td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td colspan=6 class=xl9431808>организация</td>
  <td class=xl6531808 width=30 style='width:22pt'></td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td class=xl6531808 width=57 style='width:43pt'></td>
  <td class=xl6531808 width=37 style='width:28pt'></td>
  <td class=xl6531808 width=41 style='width:31pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td class=xl6531808 width=38 style='width:29pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=50 style='width:37pt'></td>
  <td class=xl6531808 width=34 style='width:26pt'></td>
  <td class=xl6531808 width=32 style='width:24pt'></td>
  <td class=xl6531808 width=40 style='width:30pt'></td>
  <td class=xl6531808 width=30 style='width:22pt'></td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td class=xl6531808 width=57 style='width:43pt'></td>
  <td class=xl6531808 width=37 style='width:28pt'></td>
  <td class=xl6531808 width=41 style='width:31pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=8 style='mso-height-source:userset;height:6.0pt'>
  <td height=8 class=xl6531808 width=6 style='height:6.0pt;width:4pt'></td>
  <td class=xl6531808 width=38 style='width:29pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=50 style='width:37pt'></td>
  <td class=xl6531808 width=34 style='width:26pt'></td>
  <td class=xl6531808 width=32 style='width:24pt'></td>
  <td class=xl6531808 width=40 style='width:30pt'></td>
  <td class=xl6531808 width=30 style='width:22pt'></td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td class=xl6531808 width=57 style='width:43pt'></td>
  <td class=xl6531808 width=37 style='width:28pt'></td>
  <td class=xl6531808 width=41 style='width:31pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=26 style='width:19pt'></td>
  <td class=xl6531808 width=25 style='width:19pt'></td>
  <td class=xl6531808 width=36 style='width:27pt'></td>
  <td class=xl6531808 width=42 style='width:32pt'></td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=21 style='height:15.6pt'>
  <td colspan=19 height=21 class=xl9931808 width=625 style='height:15.6pt;
  width:471pt'>АКТ № {{$doc->doc_num}}</td>
 </tr>
 <tr height=44 style='mso-height-source:userset;height:33.0pt'>
  <td colspan=19 height=44 class=xl10031808 width=625 style='height:33.0pt;
  width:471pt'>на списание материалов / готовых изделий в производство</td>
 </tr>
 <tr height=37 style='mso-height-source:userset;height:27.6pt'>
  <td height=37 class=xl6531808 width=6 style='height:27.6pt;width:4pt'></td>
  <td colspan=2 class=xl9531808 width=64 style='width:49pt'>Дата составления</td>
  <td class=xl6731808 width=50 style='border-left:none;width:37pt'>Склад</td>
  <td colspan=4 class=xl9531808 width=136 style='border-left:none;width:102pt'>Структурное
  подразделение</td>
  <td colspan=5 class=xl9531808 width=189 style='border-left:none;width:143pt'>Наименование
  изделия</td>
  <td colspan=3 class=xl9531808 width=87 style='border-left:none;width:65pt'>Артикул</td>
  <td colspan=2 class=xl9531808 width=88 style='border-left:none;width:67pt'>Количество</td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=20 style='height:15.0pt'>
  <td height=20 class=xl6531808 width=6 style='height:15.0pt;width:4pt'></td>
  <td colspan=2 class=xl9631808 width=64 style='width:49pt'>
    {{$doc->doc_date_rus}}</td>
  <td class=xl6631808 width=50 style='border-left:none;width:37pt'>
    {{$doc->sklad_id}}</td>
  <td colspan=4 class=xl9731808 width=136 style='border-left:none;width:102pt'>
    {{$doc->sklad}}<span style='mso-spacerun:yes'> </span></td>
  <td colspan=5 class=xl9731808 width=189 style='border-left:none;width:143pt'>
    {{$doc->recipe}}<span style='mso-spacerun:yes'> </span></td>
  <td colspan=3 class=xl9731808 width=87 style='border-left:none;width:65pt'>
    {{$doc->product()->artikul}}</td>
  <td colspan=2 class=xl9731808 width=88 style='border-right:1.0pt solid black;
  border-left:none;width:67pt'>
    {{$doc->kolvo}}
  </td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td class=xl6531808 width=38 style='width:29pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=50 style='width:37pt'></td>
  <td class=xl6531808 width=34 style='width:26pt'></td>
  <td class=xl6531808 width=32 style='width:24pt'></td>
  <td class=xl6531808 width=40 style='width:30pt'></td>
  <td class=xl6531808 width=30 style='width:22pt'></td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td class=xl6531808 width=57 style='width:43pt'></td>
  <td class=xl6531808 width=37 style='width:28pt'></td>
  <td class=xl6531808 width=41 style='width:31pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=26 style='width:19pt'></td>
  <td class=xl6531808 width=25 style='width:19pt'></td>
  <td class=xl6531808 width=36 style='width:27pt'></td>
  <td class=xl6531808 width=42 style='width:32pt'></td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td colspan=7 class=xl8031808 width=250 style='width:188pt'>Комиссия в
  составе:</td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td class=xl6531808 width=57 style='width:43pt'></td>
  <td class=xl6531808 width=37 style='width:28pt'></td>
  <td class=xl6531808 width=41 style='width:31pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=26 style='width:19pt'></td>
  <td class=xl6531808 width=25 style='width:19pt'></td>
  <td class=xl6531808 width=36 style='width:27pt'></td>
  <td class=xl6531808 width=42 style='width:32pt'></td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='mso-height-source:userset;height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td colspan=5 class=xl8031808 width=180 style='width:136pt'>председатель
  -<span style='mso-spacerun:yes'> </span></td>
  <td colspan=5 class=xl8031808 width=192 style='width:144pt'>{{$doc->commission['commission_chairman']['name']}}</td>
  <td class=xl6531808 width=41 style='width:31pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=26 style='width:19pt'></td>
  <td class=xl6531808 width=25 style='width:19pt'></td>
  <td class=xl6531808 width=36 style='width:27pt'></td>
  <td class=xl6531808 width=42 style='width:32pt'></td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td colspan=5 class=xl8031808 width=180 style='width:136pt'>член комиссии
  -<span style='mso-spacerun:yes'> </span></td>
  <td colspan=5 class=xl8031808 width=192 style='width:144pt'>{{$doc->commission['commission_member1']['name']}}</td>
  <td class=xl6531808 width=41 style='width:31pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=26 style='width:19pt'></td>
  <td class=xl6531808 width=25 style='width:19pt'></td>
  <td class=xl6531808 width=36 style='width:27pt'></td>
  <td class=xl6531808 width=42 style='width:32pt'></td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td colspan=5 class=xl8031808 width=180 style='width:136pt'>член комиссии
  -<span style='mso-spacerun:yes'> </span></td>
  <td colspan=5 class=xl8031808 width=192 style='width:144pt'>{{$doc->commission['commission_member2']['name']}}</td>
  <td class=xl6531808 width=41 style='width:31pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=26 style='width:19pt'></td>
  <td class=xl6531808 width=25 style='width:19pt'></td>
  <td class=xl6531808 width=36 style='width:27pt'></td>
  <td class=xl6531808 width=42 style='width:32pt'></td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td colspan=10 class=xl8031808 width=372 style='width:280pt'>подтверждает
  использование следующих материалов:</td>
  <td class=xl6531808 width=41 style='width:31pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=26 style='width:19pt'></td>
  <td class=xl6531808 width=25 style='width:19pt'></td>
  <td class=xl6531808 width=36 style='width:27pt'></td>
  <td class=xl6531808 width=42 style='width:32pt'></td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td class=xl6531808 width=38 style='width:29pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=50 style='width:37pt'></td>
  <td class=xl6531808 width=34 style='width:26pt'></td>
  <td class=xl6531808 width=32 style='width:24pt'></td>
  <td class=xl6531808 width=40 style='width:30pt'></td>
  <td class=xl6531808 width=30 style='width:22pt'></td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td class=xl6531808 width=57 style='width:43pt'></td>
  <td class=xl6531808 width=37 style='width:28pt'></td>
  <td class=xl6531808 width=41 style='width:31pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=26 style='width:19pt'></td>
  <td class=xl6531808 width=25 style='width:19pt'></td>
  <td class=xl6531808 width=36 style='width:27pt'></td>
  <td class=xl6531808 width=42 style='width:32pt'></td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=34 style='mso-height-source:userset;height:25.2pt'>
  <td height=34 class=xl6531808 width=6 style='height:25.2pt;width:4pt'></td>
  <td colspan=9 class=xl6931808 width=335 style='width:252pt'>Материалы /
  готовые изделия</td>
  <td colspan=2 class=xl6931808 width=78 style='border-left:none;width:59pt'>Единица
  измерения</td>
  <td colspan=2 rowspan=2 class=xl6931808 width=52 style='width:39pt'>Количество</td>
  <td colspan=2 rowspan=2 class=xl6931808 width=61 style='width:46pt'>Цена,<br>
    руб.коп.</td>
  <td colspan=2 rowspan=2 class=xl6931808 width=88 style='width:67pt'>Сумма,<br>
    руб.коп.</td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=48 style='height:36.0pt'>
  <td height=48 class=xl6531808 width=6 style='height:36.0pt;width:4pt'></td>
  <td colspan=8 class=xl6931808 width=278 style='width:209pt'>наименование,
  обозначение</td>
  <td class=xl6931808 width=57 style='border-top:none;border-left:none;
  width:43pt'>артикул</td>
  <td class=xl6931808 width=37 style='border-top:none;border-left:none;
  width:28pt'>код</td>
  <td class=xl6931808 width=41 style='border-top:none;border-left:none;
  width:31pt'>наиме- нование</td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=20 style='height:15.0pt'>
  <td height=20 class=xl6531808 width=6 style='height:15.0pt;width:4pt'></td>
  <td colspan=8 class=xl7031808 width=278 style='width:209pt'>1</td>
  <td class=xl7031808 width=57 style='border-top:none;border-left:none;
  width:43pt'>2</td>
  <td class=xl7031808 width=37 style='border-top:none;border-left:none;
  width:28pt'>3</td>
  <td class=xl7031808 width=41 style='border-top:none;border-left:none;
  width:31pt'>4</td>
  <td colspan=2 class=xl7031808 width=52 style='border-left:none;width:39pt'>5</td>
  <td colspan=2 class=xl7031808 width=61 style='border-left:none;width:46pt'>6</td>
  <td colspan=2 class=xl7031808 width=88 style='border-left:none;width:67pt'>7</td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
@if (isset($doc_table))
    @foreach ($doc_table as $row)
        <tr height=19 style='height:14.4pt'>
            <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>

                @if ($loop->first)
                    <td colspan=8 class=xl8531808 width=278 style='width:209pt'>
                @elseif ($loop->last)
                    <td colspan=8 class=xl8531808 width=278 style='width:209pt'>
                @else
                    <td colspan=8 class=xl8531808 width=278 style='width:209pt'>
                @endif
                    {{$row['nomenklatura']}}
                </td>


                @if ($loop->first)
                    <td class=xl7231808 width=57 style='width:43pt'>
                @elseif ($loop->last)
                    <td class=xl7631808 width=57 style='border-top:none;width:43pt'>
                @else
                    <td class=xl7431808 width=57 style='border-top:none;width:43pt'>
                @endif
                    {{$row['artikul']}}
                </td>


                @if ($loop->first)
                    <td class=xl7331808 width=37 style='border-left:none;width:28pt'>
                @elseif ($loop->last)
                    <td class=xl7731808 width=37 style='border-top:none;border-left:none;width:28pt'>
                @else
                    <td class=xl7531808 width=37 style='border-top:none;border-left:none;width:28pt'>
                @endif
                    {{$row['okei']}}
                </td>


                @if ($loop->first)
                    <td class=xl7131808 width=41 style='width:31pt'>
                @elseif ($loop->last)
                    <td class=xl7131808 width=41 style='border-top:none;width:31pt'>
                @else
                    <td class=xl7131808 width=41 style='border-top:none;width:31pt'>
                @endif
                    {{$row['ed_ism']}}
                </td>


                @if ($loop->first)
                    <td colspan=2 class=xl7231808 width=52 style='width:39pt'>
                @elseif ($loop->last)
                    <td colspan=2 class=xl7631808 width=52 style='width:39pt'>
                @else
                    <td colspan=2 class=xl7431808 width=52 style='width:39pt'>
                @endif
                    {{$row['kolvo']}}
                </td>


                @if ($loop->first)
                    <td colspan=2 class=xl9131808 width=61 style='border-left:none;width:46pt'>
                @elseif ($loop->last)
                    <td colspan=2 class=xl8831808 width=61 style='border-left:none;width:46pt'>
                @else
                    <td colspan=2 class=xl8731808 width=61 style='border-left:none;width:46pt'>
                @endif
                    {{$row['price']}}
                </td>


                @if ($loop->first)
                    <td colspan=2 class=xl9131808 width=88 style='border-right:1.0pt solid black;border-left:none;width:67pt'>
                @elseif ($loop->last)
                    <td colspan=2 class=xl8931808 width=88 style='border-right:1.0pt solid black;border-left:none;width:67pt'>
                @else
                    <td colspan=2 class=xl8731808 width=88 style='border-right:1.0pt solid black;border-left:none;width:67pt'>
                @endif
                    {{$row['summa']}}
                </td>
            <td class=xl6531808 width=5 style='width:4pt'></td>
        </tr>
    @endforeach
@endif
<!--
 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td colspan=8 class=xl8531808 width=278 style='width:209pt'>Шпилька</td>
  <td class=xl7231808 width=57 style='width:43pt'>1111</td>
  <td class=xl7331808 width=37 style='border-left:none;width:28pt'>202</td>
  <td class=xl7131808 width=41 style='width:31pt'>м.</td>
  <td colspan=2 class=xl7231808 width=52 style='width:39pt'>556</td>
  <td colspan=2 class=xl9131808 width=61 style='border-left:none;width:46pt'>1000</td>
  <td colspan=2 class=xl9131808 width=88 style='border-right:1.0pt solid black;
  border-left:none;width:67pt'>55600</td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td colspan=8 class=xl8531808 width=278 style='width:209pt'>&nbsp;</td>
  <td class=xl7431808 width=57 style='border-top:none;width:43pt'>&nbsp;</td>
  <td class=xl7531808 width=37 style='border-top:none;border-left:none;width:28pt'>&nbsp;</td>
  <td class=xl7131808 width=41 style='border-top:none;width:31pt'>&nbsp;</td>
  <td colspan=2 class=xl7431808 width=52 style='width:39pt'>&nbsp;</td>
  <td colspan=2 class=xl8731808 width=61 style='border-left:none;width:46pt'>&nbsp;</td>
  <td colspan=2 class=xl8731808 width=88 style='border-right:1.0pt solid black;border-left:none;width:67pt'>&nbsp;</td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=20 style='height:15.0pt'>
  <td height=20 class=xl6531808 width=6 style='height:15.0pt;width:4pt'></td>
  <td colspan=8 class=xl8531808 width=278 style='width:209pt'>&nbsp;</td>
  <td class=xl7631808 width=57 style='border-top:none;width:43pt'>&nbsp;</td>
  <td class=xl7731808 width=37 style='border-top:none;border-left:none;width:28pt'>&nbsp;</td>
  <td class=xl7131808 width=41 style='border-top:none;width:31pt'>&nbsp;</td>
  <td colspan=2 class=xl7631808 width=52 style='width:39pt'>&nbsp;</td>
  <td colspan=2 class=xl8831808 width=61 style='border-left:none;width:46pt'>&nbsp;</td>
  <td colspan=2 class=xl8931808 width=88 style='border-right:1.0pt solid black;border-left:none;width:67pt'>&nbsp;</td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
-->
 @if (isset($itogs))

 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td class=xl6531808 width=38 style='width:29pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=50 style='width:37pt'></td>
  <td class=xl6531808 width=34 style='width:26pt'></td>
  <td class=xl6531808 width=32 style='width:24pt'></td>
  <td class=xl6531808 width=40 style='width:30pt'></td>
  <td class=xl6531808 width=30 style='width:22pt'></td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td colspan=7 class=xl8231808 width=248 style='width:187pt'>Готовые изделия:</td>
  <td colspan=2 class=xl7231808 width=88 style='border-right:1.0pt solid black; width:67pt'>
    {{$itogs->sum_production}}
  </td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=20 style='height:15.0pt'>
  <td height=20 class=xl6531808 width=6 style='height:15.0pt;width:4pt'></td>
  <td class=xl6531808 width=38 style='width:29pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=50 style='width:37pt'></td>
  <td class=xl6531808 width=34 style='width:26pt'></td>
  <td class=xl6531808 width=32 style='width:24pt'></td>
  <td class=xl6531808 width=40 style='width:30pt'></td>
  <td class=xl6531808 width=30 style='width:22pt'></td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td colspan=7 class=xl8231808 width=248 style='width:187pt'>Материалы:</td>
  <td colspan=2 class=xl7631808 width=88 style='border-right:1.0pt solid black; width:67pt'>
    {{$itogs->sum_components}}
    </td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=6 style='mso-height-source:userset;height:4.2pt'>
  <td height=6 class=xl6531808 width=6 style='height:4.2pt;width:4pt'></td>
  <td class=xl6531808 width=38 style='width:29pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=50 style='width:37pt'></td>
  <td class=xl6531808 width=34 style='width:26pt'></td>
  <td class=xl6531808 width=32 style='width:24pt'></td>
  <td class=xl6531808 width=40 style='width:30pt'></td>
  <td class=xl6531808 width=30 style='width:22pt'></td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td class=xl6531808 width=57 style='width:43pt'></td>
  <td class=xl6531808 width=37 style='width:28pt'></td>
  <td class=xl6531808 width=41 style='width:31pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=26 style='width:19pt'></td>
  <td class=xl6531808 width=25 style='width:19pt'></td>
  <td class=xl6531808 width=36 style='width:27pt'></td>
  <td class=xl6531808 width=42 style='width:32pt'></td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=20 style='height:15.0pt'>
  <td height=20 class=xl6531808 width=6 style='height:15.0pt;width:4pt'></td>
  <td class=xl6531808 width=38 style='width:29pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=50 style='width:37pt'></td>
  <td class=xl6531808 width=34 style='width:26pt'></td>
  <td class=xl6531808 width=32 style='width:24pt'></td>
  <td class=xl6531808 width=40 style='width:30pt'></td>
  <td class=xl6531808 width=30 style='width:22pt'></td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td colspan=7 class=xl8231808 width=248 style='width:187pt'>Всего:</td>
  <td colspan=2 class=xl8331808 width=88 style='border-right:1.0pt solid black;width:67pt'>
    {{$itogs->total}}
    </td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 @endif
 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td class=xl6531808 width=38 style='width:29pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=50 style='width:37pt'></td>
  <td class=xl6531808 width=34 style='width:26pt'></td>
  <td class=xl6531808 width=32 style='width:24pt'></td>
  <td class=xl6531808 width=40 style='width:30pt'></td>
  <td class=xl6531808 width=30 style='width:22pt'></td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td class=xl6531808 width=57 style='width:43pt'></td>
  <td class=xl6531808 width=37 style='width:28pt'></td>
  <td class=xl6531808 width=41 style='width:31pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=26 style='width:19pt'></td>
  <td class=xl6531808 width=25 style='width:19pt'></td>
  <td class=xl6531808 width=36 style='width:27pt'></td>
  <td class=xl6531808 width=42 style='width:32pt'></td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='mso-height-source:userset;height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td colspan=3 class=xl8031808 width=114 style='width:86pt'>Председатель</td>
  <td colspan=4 class=xl8131808 width=136 style='width:102pt'>{{$doc->commission['commission_chairman']['position']}}</td>
  <td class=xl6831808 width=28 style='width:21pt'></td>
  <td colspan=3 class=xl8131808 width=135 style='width:102pt'>&nbsp;</td>
  <td class=xl6831808 width=26 style='width:20pt'></td>
  <td colspan=4 class=xl8131808 width=129 style='width:97pt'>{{$doc->commission['commission_chairman']['name']}}</td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:10.2pt'>
  <td height=14 class=xl6531808 width=6 style='height:10.2pt;width:4pt'></td>
  <td class=xl6531808 width=38 style='width:29pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=50 style='width:37pt'></td>
  <td colspan=4 class=xl7831808 width=136 style='width:102pt'>(должность)</td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td colspan=3 class=xl7831808 width=135 style='width:102pt'>(подпись)</td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td colspan=4 class=xl7931808 width=129 style='width:97pt'>(расшифровка
  подписи)</td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td colspan=3 class=xl8031808 width=114 style='width:86pt'>Член комиссии</td>
  <td colspan=4 class=xl8131808 width=136 style='width:102pt'>{{$doc->commission['commission_member1']['position']}}</td>
  <td class=xl6831808 width=28 style='width:21pt'></td>
  <td colspan=3 class=xl8131808 width=135 style='width:102pt'>&nbsp;</td>
  <td class=xl6831808 width=26 style='width:20pt'></td>
  <td colspan=4 class=xl8131808 width=129 style='width:97pt'>{{$doc->commission['commission_member1']['name']}}</td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=14 style='mso-height-source:userset;height:10.8pt'>
  <td height=14 class=xl6531808 width=6 style='height:10.8pt;width:4pt'></td>
  <td class=xl6531808 width=38 style='width:29pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=50 style='width:37pt'></td>
  <td colspan=4 class=xl7831808 width=136 style='width:102pt'>(должность)</td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td colspan=3 class=xl7831808 width=135 style='width:102pt'>(подпись)</td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td colspan=4 class=xl7931808 width=129 style='width:97pt'>(расшифровка
  подписи)</td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td colspan=3 class=xl8031808 width=114 style='width:86pt'>Член комиссии</td>
  <td colspan=4 class=xl8131808 width=136 style='width:102pt'>{{$doc->commission['commission_member2']['position']}}</td>
  <td class=xl6831808 width=28 style='width:21pt'></td>
  <td colspan=3 class=xl8131808 width=135 style='width:102pt'>&nbsp;</td>
  <td class=xl6831808 width=26 style='width:20pt'></td>
  <td colspan=4 class=xl8131808 width=129 style='width:97pt'>{{$doc->commission['commission_member2']['name']}}</td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>
 <tr height=19 style='height:14.4pt'>
  <td height=19 class=xl6531808 width=6 style='height:14.4pt;width:4pt'></td>
  <td class=xl6531808 width=38 style='width:29pt'></td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td class=xl6531808 width=50 style='width:37pt'></td>
  <td colspan=4 class=xl7831808 width=136 style='width:102pt'>(должность)</td>
  <td class=xl6531808 width=28 style='width:21pt'></td>
  <td colspan=3 class=xl7831808 width=135 style='width:102pt'>(подпись)</td>
  <td class=xl6531808 width=26 style='width:20pt'></td>
  <td colspan=4 class=xl7931808 width=129 style='width:97pt'>(расшифровка
  подписи)</td>
  <td class=xl6531808 width=46 style='width:35pt'></td>
  <td class=xl6531808 width=5 style='width:4pt'></td>
 </tr>

</table>

</div>
@endsection


