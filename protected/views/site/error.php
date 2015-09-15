<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ro-RO">
<head profile="http://gmpg.org/xfn/11">
<style type="text/css">
/** 
 *
 * Robotik HTML Error Pages v 1.1
 * Developed by MogooLab - www.mogoolab.com
 *
 **/


/* =Reset default browser CSS. Based on work by Eric Meyer: http://meyerweb.com/eric/tools/css/reset/index.html
-------------------------------------------------------------- */

html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, font, ins, kbd, menu, q, s, samp,
small, strike, strong, sub, sup, tt, var,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td {
    border: 0;
    font-family: inherit;
    font-size: 100%;
    font-style: inherit;
    font-weight: inherit;
    margin: 0;
    outline: 0;
    padding: 0;
    vertical-align: baseline;
}
:focus {
        outline: 0;
}
body {
    background: #fff;
    line-height: 1;
}
ol, ul, menu {
    list-style: none;
}
table {
        border-collapse: separate;
    border-spacing: 0;
}
caption, th, td {
    font-weight: normal;
    text-align: left;
}
blockquote:before, blockquote:after,
q:before, q:after {
    content: "";
}
blockquote, q {
    quotes: "" "";
}
a img {
    border: 0;
}
article, aside, details, figcaption, figure,
footer, header, hgroup, menu, nav, section {
    display: block;
}

a, a:hover, a:active {
    text-decoration:none;
}



/******************************************************************************/

body, html {
  width:100%;
  margin:0 auto;
  padding:0;
}

body {
  background: #f2f2f2 url("<?php echo Yii::app()->request->baseUrl; ?>/imgs/error/background.png") top left;
}

/* page wrappers **************************************************************/


/* page Wrapper */
.wrapper { 
    width:100%;
    margin:-200px auto 0;
    display:table;
    position:absolute;
    top:50%;
    background: url("<?php echo Yii::app()->request->baseUrl; ?>/imgs/error/container_background.png") repeat-x scroll left top transparent;
}

/* content wrapper */
.mainWrapper {
    margin: 0 auto;
    position: relative;
    width: 830px;   
}

/* right holder - Message, Robot, Try to, Search Form */
.rightHolder {
    display: block;
    margin: 75px auto;
    height: 351px;
    width: 410px;
}

/* robot message holder */
.message {
    background: url("<?php echo Yii::app()->request->baseUrl; ?>/imgs/error/message_stick.png") no-repeat scroll 41px 100% transparent;
    display: block;
    float: right;
    font-family: 'Istok web',sans-serif;
    font-size: 14px;
    line-height: 22px;
    margin: -30px 0 30px;
    overflow: hidden;
    padding: 0 0 10px;
    position: relative;
}
/* robot message text */
.message p {
    background: none repeat scroll 0 0 #41444B;
    border-radius: 10px 10px 10px 10px;
    color: #FFFFFF;
    padding: 10px;
    width: 319px;
}

.robotik {
    display: block;
    float: left;
    height: 129px;
    margin: 0 19px 47px 72px;
    width: 126px;
}
</style>

<!--[if IE]>
<script type="text/javascript" src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

</head>

<body>


<div class="wrapper">

	<div class="mainWrapper">
        <div class="rightHolder">
            <div class="message"><p><?php if($code != 403)echo CHtml::encode($message) .' - '. $code; ?> You don't have permission to access this page.</p></div>
            <div class="robotik"><img src="<?php echo Yii::app()->request->baseUrl; ?>/imgs/error/robotik.png" alt="Oooops....we can’t find that page." title="Oooops....we can’t find that page." id="robot"></div>
      	</div>
	</div>

</div>
<!-- end .wrapper -->

</body>
</html>