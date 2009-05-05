<?php
			ob_start("ob_gzhandler");
			header("Content-type: text/css; charset: UTF-8");
			header("Cache-Control: must-revalidate");
			?>body{
	width:850px; 
	margin: 0 auto;
	font-family: Verdana, sans-serif;
	font-size: 9pt;
	color: #fff;
	line-height: 1.7em;
	background-color: #292929;
}
a:link{
	color: white;
	text-decoration: none;
}
a:active{
	color: red;
}
a:visited{
	color: white;
	text-decoration: none;
}
a img{
	border: none;
}
#header{
	margin-bottom: 15px;
	position: relative;
}
#banner{
	background: url('../images/wcms_banner.png');
	height:65px;
}
#sitename{
	font-size: 25px;
	font-weight: bold;
	text-align: center;
	position: relative;
	top: 15px;
}
#siteslogan{
	font-family:arial;
	font-size:15px;
	padding-right:10px;
	position:relative;
	text-align:right;
	top:25px;
}
#user{
	float:right;
	padding-right:5px;
	position:relative;
	top:46px;
}
#header #rbbox{
	position: absolute;
	right: 10px;
	top: 20px;
	font-size: 10px;
}
#rbbox input, #loginform form input{
	font-size: 10px;
}
#menu{
	position: absolute;
	top: 46px;
	padding-left: 35px;
}
#menu a:link,#menu a:visited {
	color: #fff;
}
#loading{
	position: absolute;
	left: 10%;
	top: 35%;
}
#messages{
	background:#4D5050 none repeat scroll 0 0;
	border:1px solid #FFFFFF;
	margin:0 50px 15px;
	padding:5px 0;
}
#messages ul{
	list-style: none;
}
.messages .status {
    background:transparent url(../images/list-status.png) no-repeat scroll -2px 50%;
	padding-left: 30px;
}
.messages .info{
	background:transparent url(../images/list-info.png) no-repeat scroll -2px 50%;
	padding-left: 30px;
}
.messages .error{
	background:transparent url(../images/list-error.png) no-repeat scroll -2px 50%;
	padding-left: 30px;
}
#main{
	float: left;
	width:660px;
}
#iconmenu{
	height:60px;
	text-align: center;
	margin-bottom: 30px;
}
#iconmenu a{
	display:inline-block;
	height:48px;
	padding:0 10px;
}

#iconmenu a:hover span, #iconmenu a.active{
	color: #179AFC;
}

#sidebar-right{
	margin-left:660px;
	padding:0 10px;
	width:180px;
	/*
border: 1px dashed gray;
*/
}
.block-body{
	background-color: #3c4040;
	margin-bottom:10px;
	padding:5px;
}
.post{
	margin-bottom:15px;
	padding-bottom:20px;
	margin-right: 5px;
	background-color: #3c4040;
	/*-moz-opacity: .85;
	filter: alpha(opacity=75);*/
}
div.contentheader{
	background: transparent url(../images/contentheader.png) repeat-x scroll;
	height: 23px;
}
.content{
	padding: 5px 5px 20px;
	border-bottom: 1px solid gray;
	overflow: hidden;
}
.content a.postImage { 
	float: left; 
	padding: 3px 3px 0 3px;
	border: 1px solid black;
	background-color: white;
	margin-right: 5px;
} 
.content a.postImage img { 
	/* no float */ 
}
.links{
	padding: 0 10px;
}
.links .comments{
	float: left;
}
.links .submitted{
	float: right;
}
h2.title, h2.title a {
	margin-top: 0;
	font-family: sans;
	font-size: 14px;
	color: #fff;
	text-indent: 1em;
	text-decoration: none;
}
#footer{
	text-align:center;
	clear: both;
}
/* tables */
table.tablesorter {
	font-family:arial;
	color: #000;
	background-color: #CDCDCD;
	margin:10px 0pt 15px;
	font-size: 8pt;
	width: 100%;
	text-align: left;
}
table.tablesorter thead tr th, table.tablesorter tfoot tr th {
	background-color: #e6EEEE;
	border: 1px solid #FFF;
	font-size: 8pt;
	padding: 4px;
}
table.tablesorter thead tr .header {
	background-image: url(bg.gif);
	background-repeat: no-repeat;
	background-position: center right;
	cursor: pointer;
}
table.tablesorter tbody td {
	color: #3D3D3D;
	padding: 4px;
	background-color: #FFF;
	vertical-align: top;
}
table.tablesorter tbody tr.odd td {
	background-color:#F0F0F6;
}
table.tablesorter thead tr .headerSortUp {
	background-image: url(asc.gif);
}
table.tablesorter thead tr .headerSortDown {
	background-image: url(desc.gif);
}
table.tablesorter thead tr .headerSortDown, table.tablesorter thead tr .headerSortUp {
	background-color: #8dbdd8;
}
table.tablesorter tbody td.actions{
	text-align: center;
}
.formheader{
	text-align: center;
	font-weight:bold;
	background: transparent url(../images/contentheader.png) repeat-x scroll;
}
.elementcontainer{
	padding: 15px 10px 10px;
}
div.label{
	float:left;
	width:150px;
	font-weight: bold;
}
div.formbutton{
	text-align: center;
}
#mainAdmin{
	padding: 15px 0;
}
.imgTitle{
	clear:both;
	display:block;
	font-weight:bold;
	line-height:0.8em;
}
/*
Begin Sortables Styles
*/

.groupWrapper
{
	width: 315px;
	float: left;
	margin-right: 5px;
	min-height: 400px;
}

#sidebar
{
	width: 15%;
	float: right;
	margin-left: 1%;
	min-height: 200px;
	background-color: gray;
	display: none;
}
#sidebar .groupItem {
	height: 50px;
}
.serializer
{
	float: left;
}

.groupItem
{
	margin-bottom: 20px;
	background: #3C4040;
}
.groupItem .itemHeader
{
	line-height: 20px;
	background: transparent url(../images/contentheader.png) repeat-x scroll 0 0;
	color: #fff;
	padding: 0 10px;
	cursor: move;
	font-weight: bold;
	font-size: 16px;
	height: 20px;
	position: relative;
}

.closeEl
{
	position: absolute;
	right: 10px;
	top: 0px;
	font-weight: normal;
	font-size: 11px;
	text-decoration: none;
}

.edit
{
	position: absolute;
	right: 40px;
	top: 0px;
	font-weight: normal;
	font-size: 11px;
	text-decoration: none;
}
.sortHelper
{
	background-color: lightblue;
	border: 1px dashed #666;
	width: 100px !important;
}
.groupWrapper p
{
	height: 1px;
	overflow: hidden;
	margin: 0;
	padding: 0;
}
#addContent {
	position: relative;
}
.nicEdit-selected, .nicEdit-main {
	background-color: #fff;
	margin-left: 130px;
}
.nicEdit-panelContain, .nicEdit-pane{
	color: #000;
	line-height: 1em;
}

.element{
	margin-left: 130px;
}
.section {
	background-color: #3C4040;
	margin-bottom:	10px;
	padding-bottom: 10px;
}
/* Sitewide Dialogs requiring Input */

.ui-dialog {
	background-color: #4D5050;
}

.ui-dialog .ui-dialog-titlebar {
	border-bottom: 1px solid #fff;
	background: #aaa repeat-x;
	padding: 0px;
	height: 22px;
	background: transparent url(../images/contentheader.png) repeat-x scroll 0 0;
}

.dialog .ui-draggable .ui-dialog-titlebar,
.dialog.ui-draggable .ui-dialog-titlebar {
	cursor: move;
}

.dialog .ui-draggable-disabled .ui-dialog-titlebar,
.dialog.ui-draggable-disabled .ui-dialog-titlebar {
	cursor: default;
}

.dialog .ui-dialog .ui-dialog-titlebar-close,
.dialog.ui-dialog .ui-dialog-titlebar-close {
	width: 16px;
	height: 16px;
	position:absolute;
	top: 6px;
	right: 7px;
	cursor: default;
}

.dialog .ui-dialog .ui-dialog-titlebar-close span,
.dialog.ui-dialog .ui-dialog-titlebar-close span {
	display: none;
}


.dialog .ui-dialog .ui-dialog-titlebar-close-hover,
.dialog.ui-dialog .ui-dialog-titlebar-close-hover {
	background: url(../images/dialog-titlebar-close-hover.png) no-repeat;
}


.ui-dialog .ui-dialog-title {
	margin-left: 5px;
	color: white;
	font-weight: bold;
	position: relative;
	top: 2px;
	left: 4px;
}

/*
.dialog .ui-dialog .ui-dialog-content,
.dialog.ui-dialog .ui-dialog-content {
	margin: 1.2em;
}
*/

.dialog .ui-dialog .ui-dialog-buttonpane,
.dialog.ui-dialog .ui-dialog-buttonpane {
	position: absolute;
	bottom: 8px;
	right: 12px;
	width: 100%;
	text-align: right;
}

.dialog .ui-dialog .ui-dialog-buttonpane button,
.dialog.ui-dialog .ui-dialog-buttonpane button {
	margin: 6px;
}

/* Dialog handle styles */
.dialog .ui-dialog .ui-resizable-n,
.dialog.ui-dialog .ui-resizable-n { cursor: n-resize; height: 6px; width: 100%; top: 0px; left: 0px; background: transparent url(i/dialog-n.gif) repeat scroll center top; }

.dialog .ui-dialog .ui-resizable-s,
.dialog.ui-dialog .ui-resizable-s { cursor: s-resize; height: 8px; width: 100%; bottom: 0px; left: 0px; background: transparent url(i/dialog-s.gif) repeat scroll center top; }

.dialog .ui-dialog .ui-resizable-e,
.dialog.ui-dialog .ui-resizable-e { cursor: e-resize; width: 7px; right: 0px; top: 0px; height: 100%; background: transparent url(i/dialog-e.gif) repeat scroll right center; }

.dialog .ui-dialog .ui-resizable-w,
.dialog.ui-dialog .ui-resizable-w { cursor: w-resize; width: 7px; left: 0px; top: 0px; height: 100%; background: transparent url(i/dialog-w.gif) repeat scroll right center; }

.dialog .ui-dialog .ui-resizable-se,
.dialog.ui-dialog .ui-resizable-se { cursor: se-resize; width: 9px; height: 9px; right: 0px; bottom: 0px; background: transparent url(i/dialog-se.gif); }

.dialog .ui-dialog .ui-resizable-sw,
.dialog.ui-dialog .ui-resizable-sw { cursor: sw-resize; width: 9px; height: 9px; left: 0px; bottom: 0px; background: transparent url(i/dialog-sw.gif); }

.dialog .ui-dialog .ui-resizable-nw,
.dialog.ui-dialog .ui-resizable-nw { cursor: nw-resize; width: 9px; height: 29px; left: 0px; top: 0px; background: transparent url(i/dialog-nw.gif); }

.dialog .ui-dialog .ui-resizable-ne,
.dialog.ui-dialog .ui-resizable-ne { cursor: ne-resize; width: 9px; height: 29px; right: 0px; top: 0px; background: transparent url(i/dialog-ne.gif); }

/* loading dialog styles */

.loading .ui-dialog,
.loading.ui-dialog {
	background-color: #4D5050;
}

.loading .ui-dialog .ui-dialog-titlebar-close,
.loading.ui-dialog .ui-dialog-titlebar-close {
	display: none;
}

.loading .ui-dialog .ui-dialog-titlebar-close span,
.loading.ui-dialog .ui-dialog-titlebar-close span {
	display: none;
}

.loading .ui-dialog .ui-dialog-content,
.loading.ui-dialog .ui-dialog-content {
	margin: 1.2em;
}

.loading .ui-dialog .ui-dialog-buttonpane,
.loading.ui-dialog .ui-dialog-buttonpane {
	position: absolute;
	bottom: 8px;
	right: 12px;
	width: 100%;
	text-align: right;
}

.loading .ui-dialog .ui-dialog-buttonpane button,
.loading.ui-dialog .ui-dialog-buttonpane button {
	margin: 6px;
}

/* ----------------------------------------------------------------------------------------------------------------*/
/* ---------->>> global settings needed for thickbox <<<-----------------------------------------------------------*/
/* ----------------------------------------------------------------------------------------------------------------*/
*{padding: 0; margin: 0;}

/* ----------------------------------------------------------------------------------------------------------------*/
/* ---------->>> thickbox specific link and font settings <<<------------------------------------------------------*/
/* ----------------------------------------------------------------------------------------------------------------*/
#TB_window {
	font: 12px Arial, Helvetica, sans-serif;
	color: #333333;
}

#TB_secondLine {
	font: 10px Arial, Helvetica, sans-serif;
	color:#666666;
}

#TB_window a:link {color: #666666;}
#TB_window a:visited {color: #666666;}
#TB_window a:hover {color: #000;}
#TB_window a:active {color: #666666;}
#TB_window a:focus{color: #666666;}

/* ----------------------------------------------------------------------------------------------------------------*/
/* ---------->>> thickbox settings <<<-----------------------------------------------------------------------------*/
/* ----------------------------------------------------------------------------------------------------------------*/
#TB_overlay {
	position: fixed;
	z-index:100;
	top: 0px;
	left: 0px;
	height:100%;
	width:100%;
}

.TB_overlayMacFFBGHack {background: url(macFFBgHack.png) repeat;}
.TB_overlayBG {
	background-color:#000;
	filter:alpha(opacity=75);
	-moz-opacity: 0.75;
	opacity: 0.75;
}

* html #TB_overlay { /* ie6 hack */
     position: absolute;
     height: expression(document.body.scrollHeight > document.body.offsetHeight ? document.body.scrollHeight : document.body.offsetHeight + 'px');
}

#TB_window {
	position: fixed;
	background: #ffffff;
	z-index: 102;
	color:#000000;
	display:none;
	border: 4px solid #525252;
	text-align:left;
	top:50%;
	left:50%;
}

* html #TB_window { /* ie6 hack */
position: absolute;
margin-top: expression(0 - parseInt(this.offsetHeight / 2) + (TBWindowMargin = document.documentElement && document.documentElement.scrollTop || document.body.scrollTop) + 'px');
}

#TB_window img#TB_Image {
	display:block;
	margin: 15px 0 0 15px;
	border-right: 1px solid #ccc;
	border-bottom: 1px solid #ccc;
	border-top: 1px solid #666;
	border-left: 1px solid #666;
}

#TB_caption{
	height:25px;
	padding:7px 30px 10px 25px;
	float:left;
}

#TB_closeWindow{
	height:25px;
	padding:11px 25px 10px 0;
	float:right;
}

#TB_closeAjaxWindow{
	padding:7px 10px 5px 0;
	margin-bottom:1px;
	text-align:right;
	float:right;
}

#TB_ajaxWindowTitle{
	float:left;
	padding:7px 0 5px 10px;
	margin-bottom:1px;
}

#TB_title{
	background-color:#e8e8e8;
	height:27px;
}

#TB_ajaxContent{
	clear:both;
	padding:2px 15px 15px 15px;
	overflow:auto;
	text-align:left;
	line-height:1.4em;
}

#TB_ajaxContent.TB_modal{
	padding:15px;
}

#TB_ajaxContent p{
	padding:5px 0px 5px 0px;
}

#TB_load{
	position: fixed;
	display:none;
	height:13px;
	width:208px;
	z-index:103;
	top: 50%;
	left: 50%;
	margin: -6px 0 0 -104px; /* -height/2 0 0 -width/2 */
}

* html #TB_load { /* ie6 hack */
position: absolute;
margin-top: expression(0 - parseInt(this.offsetHeight / 2) + (TBWindowMargin = document.documentElement && document.documentElement.scrollTop || document.body.scrollTop) + 'px');
}

#TB_HideSelect{
	z-index:99;
	position:fixed;
	top: 0;
	left: 0;
	background-color:#fff;
	border:none;
	filter:alpha(opacity=0);
	-moz-opacity: 0;
	opacity: 0;
	height:100%;
	width:100%;
}

* html #TB_HideSelect { /* ie6 hack */
     position: absolute;
     height: expression(document.body.scrollHeight > document.body.offsetHeight ? document.body.scrollHeight : document.body.offsetHeight + 'px');
}

#TB_iframeContent{
	clear:both;
	border:none;
	margin-bottom:-1px;
	margin-top:1px;
	_margin-bottom:1px;
}
@import "flora.css";

/* Caution! Ensure accessibility in print and other media types... */
@media projection, screen { /* Use class for showing/hiding tab content, so that visibility can be better controlled in different media types... */
    .ui-tabs-hide {
        display: none !important;
    }
}

/* Hide useless elements in print layouts... */
@media print {
    .ui-tabs-nav {
        display: none;
    }
}

/* Skin */
.ui-tabs-nav, .ui-tabs-panel {
    font-family: "Trebuchet MS", Trebuchet, Verdana, Helvetica, Arial, sans-serif;
    font-size: 12px;
}
.ui-tabs-nav {
    list-style: none;
    margin: 0;
    padding: 0 0 0 3px;
}
.ui-tabs-nav:after { /* clearing without presentational markup, IE gets extra treatment */
    display: block;
    clear: both;
    content: " ";
}
.ui-tabs-nav li {
    float: left;
    margin: 0 0 0 2px;
    font-weight: bold;
}
.ui-tabs-nav a, .ui-tabs-nav a span {
    float: left; /* fixes dir=ltr problem and other quirks IE */
    padding: 0 12px;
    background: #179afc no-repeat;
}
.ui-tabs-nav a {
    margin: 5px 0 0; /* position: relative makes opacity fail for disabled tab in IE */
    padding-left: 5px;
    background-position: 100% 0;
    text-decoration: none;
    white-space: nowrap; /* @ IE 6 */
    outline: 0; /* @ Firefox, prevent dotted border after click */    
}
.ui-tabs-nav a:link, .ui-tabs-nav a:visited {
    color: #fff;
}
.ui-tabs-nav .ui-tabs-selected a {
    position: relative;
    top: 1px;
    z-index: 2;
    margin-top: 0;
	background-color: #017DF9;
    background-position: 100% -23px;
}
.ui-tabs-nav a span {
    padding-top: 1px;
    padding-right: 0;
    height: 20px;
    background-position: 0 0;
    line-height: 20px;
}
.ui-tabs-nav .ui-tabs-selected a span {
    padding-top: 0;
    height: 27px;
    line-height: 27px;
}
.ui-tabs-nav .ui-tabs-selected a:link, .ui-tabs-nav .ui-tabs-selected a:visited,
.ui-tabs-nav .ui-tabs-disabled a:link, .ui-tabs-nav .ui-tabs-disabled a:visited { /* @ Opera, use pseudo classes otherwise it confuses cursor... */
    cursor: text;
}
.ui-tabs-nav a:hover, .ui-tabs-nav a:focus, .ui-tabs-nav a:active,
.ui-tabs-nav .ui-tabs-unselect a:hover, .ui-tabs-nav .ui-tabs-unselect a:focus, .ui-tabs-nav .ui-tabs-unselect a:active { /* @ Opera, we need to be explicit again here now... */
    cursor: pointer;
}
.ui-tabs-disabled {
    opacity: .4;
    filter: alpha(opacity=40);
}
.ui-tabs-nav .ui-tabs-disabled a:link, .ui-tabs-nav .ui-tabs-disabled a:visited {
    color: #000;
}
/*
.ui-tabs-panel {
    border: 1px solid #519e2d;
    padding: 10px;
    background: #fff; /* declare background color for container to avoid distorted fonts in IE while fading */
}
*/
/*.ui-tabs-loading em {
    padding: 0 0 0 20px;
    background: url(loading.gif) no-repeat 0 50%;
}*/

/* Additional IE specific bug fixes... */
* html .ui-tabs-nav { /* auto clear @ IE 6 & IE 7 Quirks Mode */
    display: inline-block;
}
*:first-child+html .ui-tabs-nav  { /* auto clear @ IE 7 Standards Mode - do not group selectors, otherwise IE 6 will ignore complete rule (because of the unknown + combinator)... */
    display: inline-block;
}
/* Main Flora Style Sheet for jQuery UI ui-datepicker */
.hasDatepicker{
	height: 210px;
}
#ui-datepicker-div, .ui-datepicker-inline {
	font-family: Arial,Helvetica,sans-serif;
	font-size: 14px;
	padding: 0;
	margin: 0;
	/*background: #E6EEEE;*/
	width: 170px;
}
#ui-datepicker-div {
	display: none;
	border: 1px solid #FF9900;
	z-index: 10;
}
.ui-datepicker-inline {
	/*float: left;*/
	display: block;
	border: 0;
}
.ui-datepicker-rtl {
	direction: rtl;
}
.ui-datepicker-dialog {
	padding: 5px !important;
	border: 4px ridge #017DF9 !important;
}
.ui-datepicker-disabled {
	position: absolute;
	z-index: 10;
	background-color: white;
	opacity: 0.5;
}
button.ui-datepicker-trigger {
	width: 25px;
}
img.ui-datepicker-trigger {
	margin: 2px;
	vertical-align: middle;
}
.ui-datepicker-prompt {
	float: left;
	padding: 2px;
	background: #E6EEEE;
	color: #000;
}
*html .ui-datepicker-prompt {
	width: 175px;
}
.ui-datepicker-control, .ui-datepicker-links, .ui-datepicker-header, .ui-datepicker {
	/*
clear: both;
*/
	float: left;
	width: 100%;
	color: #FFF;
}
.ui-datepicker-control {
	background: #FF9900;
	padding: 2px 0px;
}
.ui-datepicker-links {
	background: #E6EEEE;
	padding: 2px 0px;
}
.ui-datepicker-control, .ui-datepicker-links {
	font-weight: bold;
	font-size: 80%;
	/*letter-spacing: 1px;*/
}
.ui-datepicker-links label {
	padding: 2px 5px;
	color: #888;
}
.ui-datepicker-clear, .ui-datepicker-prev {
	float: left;
	width: 34%;
}
.ui-datepicker-rtl .ui-datepicker-clear, .ui-datepicker-rtl .ui-datepicker-prev {
	float: right;
	text-align: right;
}
.ui-datepicker-current {
	float: left;
	width: 30%;
	text-align: center;
}
.ui-datepicker-close, .ui-datepicker-next {
	float: right;
	width: 34%;
	text-align: right;
}
.ui-datepicker-rtl .ui-datepicker-close, .ui-datepicker-rtl .ui-datepicker-next {
	float: left;
	text-align: left;
}
.ui-datepicker-header {
	padding: 1px 0 3px;
	background: #017DF9;
	text-align: center;
	font-weight: bold;
	height: 1.3em;
}
.ui-datepicker-header select {
	background: #017DF9;
	color: #000;
	border: 0px;
	/*font-weight: bold;*/
}
.ui-datepicker {
	background: #CCC;
	text-align: center;
	font-size: 100%;
}
.ui-datepicker a {
	display: block;
	width: 100%;
}
.ui-datepicker-title-row {
	background: #179AFC;
	color: #000;
}
.ui-datepicker-title-row .ui-datepicker-week-end-cell {
	background: #179AFC;
}
.ui-datepicker-days-row {
	background: #FFF;
	color: #666;
}
.ui-datepicker-week-col {
	background: #179AFC;
	color: #000;
}
.ui-datepicker-days-cell {
	color: #000;
	border: 1px solid #DDD;
}
.ui-datepicker-days-cell a {
	display: block;
}
.ui-datepicker-week-end-cell {
	background: #E6EEEE;
}
.ui-datepicker-unselectable {
	color: #888;
}
.ui-datepicker-week-over, .ui-datepicker-week-over .ui-datepicker-week-end-cell {
	background: #179AFC !important;
}
.ui-datepicker-days-cell-over, .ui-datepicker-days-cell-over.ui-datepicker-week-end-cell {
	background: #FFF !important;
	border: 1px solid #777;
}
* html .ui-datepicker-title-row .ui-datepicker-week-end-cell {
	background: #179AFC !important;
}
* html .ui-datepicker-week-end-cell {
	background: #E6EEEE !important;
	border: 1px solid #DDD !important;
}
* html .ui-datepicker-days-cell-over {
	background: #FFF !important;
	border: 1px solid #777 !important;
}
* html .ui-datepicker-current-day {
	background: #017DF9 !important;
}
.ui-datepicker-today {
	background: #179AFC !important;
}
.ui-datepicker-current-day {
	background: #017DF9 !important;
}
.ui-datepicker-status {
	background: #E6EEEE;
	width: 100%;
	font-size: 80%;
	text-align: center;
}
#ui-datepicker-div a, .ui-datepicker-inline a {
	cursor: pointer;
	margin: 0;
	padding: 0;
	background: none;
	color: #000;
}
.ui-datepicker-inline .ui-datepicker-links a {
	padding: 0 5px !important;
}
.ui-datepicker-control a, .ui-datepicker-links a {
	padding: 2px 5px !important;
	color: #000 !important;
}
.ui-datepicker-title-row a {
	color: #000 !important;
}
.ui-datepicker-control a:hover {
	background: #FDD !important;
	color: #333 !important;
}
.ui-datepicker-links a:hover, .ui-datepicker-title-row a:hover {
	background: #FFF !important;
	color: #333 !important;
}
.ui-datepicker-multi .ui-datepicker {
	border: 1px solid #017DF9;
}
.ui-datepicker-one-month {
	float: left;
	width: 170px;
}
.ui-datepicker-cover {
	display: none;
	display/**/: block;
	position: absolute;
	z-index: -1;
	filter: mask();
	top: -4px;
	left: -4px;
	width: 193px;
	height: 200px;
}
