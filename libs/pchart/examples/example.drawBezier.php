<?php   
 /* @ 700x230 Simple bezier curve drawing example. */

 /* pChart library inclusions */
 include("../class/pDraw.class");
 include("../class/pImage.class");

 /* Create the pChart object */
 $myPicture = new pImage(700,230);
 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,array("StartR"=>180,"StartG"=>193,"StartB"=>91,"EndR"=>120,"EndG"=>137,"EndB"=>72,"Alpha"=>100));
 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_HORIZONTAL,array("StartR"=>180,"StartG"=>193,"StartB"=>91,"EndR"=>120,"EndG"=>137,"EndB"=>72,"Alpha"=>20));
 $myPicture->drawGradientArea(0,0,700,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));
 
 /* Write the picture title */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Silkscreen.ttf","FontSize"=>6));
 $myPicture->drawText(10,13,"drawBezier() - some cubic curves",array("R"=>255,"G"=>255,"B"=>255));

 /* Turn on shadow computing */ 
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));

 /* Draw one bezier curve */ 
 $BezierSettings = array("R"=>255,"G"=>255,"B"=>255,"ShowControl"=>TRUE);
 $myPicture->drawBezier(20,40,280,170,130,160,160,60,$BezierSettings);

 /* Draw one bezier curve */ 
 $BezierSettings = array("R"=>255,"G"=>255,"B"=>255,"ShowControl"=>TRUE,"Ticks"=>4,"DrawArrow"=>TRUE,"ArrowTwoHeads"=>TRUE);
 $myPicture->drawBezier(360,120,630,120,430,50,560,190,$BezierSettings);

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.drawBezier.png");
?>