<?php   
 /* @ 700x230 Rounded rectangle drawing example. */

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
 $myPicture->drawText(10,13,"drawRoundedRectangle() - Transparency & colors",array("R"=>255,"G"=>255,"B"=>255));

 /* Enable shadow computing */
 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));

 /* Draw a rounded rectangle */
 $RectangleSettings = array("R"=>181,"G"=>209,"B"=>27,"Alpha"=>100);
 $myPicture->drawRoundedRectangle(20,60,400,170,10,$RectangleSettings);

 /* Draw a rounded rectangle */
 $RectangleSettings = array("R"=>209,"G"=>134,"B"=>27,"Alpha"=>30);
 $myPicture->drawRoundedRectangle(30,30,200,200,10,$RectangleSettings);

 /* Draw a rounded rectangle */
 $RectangleSettings = array("R"=>209,"G"=>31,"B"=>27,"Alpha"=>100);
 $myPicture->drawRoundedRectangle(480,50,650,80,5,$RectangleSettings);

 /* Draw a rounded rectangle */
 $RectangleSettings = array("R"=>209,"G"=>125,"B"=>27,"Alpha"=>100);
 $myPicture->drawRoundedRectangle(480,90,650,120,5,$RectangleSettings);

 /* Draw a rounded rectangle */
 $RectangleSettings = array("R"=>209,"G"=>198,"B"=>27,"Alpha"=>100);
 $myPicture->drawRoundedRectangle(480,130,650,160,5,$RectangleSettings);

 /* Draw a rounded rectangle */
 $RectangleSettings = array("R"=>134,"G"=>209,"B"=>27,"Alpha"=>100);
 $myPicture->drawRoundedRectangle(480,170,650,200,5,$RectangleSettings);

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.drawRoundedRectangle.png");
?>