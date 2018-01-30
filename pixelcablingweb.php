<?php

header("Content-type: text/html");

$execOut = "";
$execErr = "";

$cablingTxt = "";

$searchOption = "rawid";
$outDicTxtFileName = "/tmp/pixelcablingweb_cablingInfo.dat";

$intervalBetweenReproduction = 3600;

$gtPlaceholder = "92X_upgrade2017_realistic_v11";

if (isset($_POST["getCabling"]))
{
  $cablingTxt   = $_POST["getCabling"];
  $entity_id    = $_POST["entity_id"];
  $searchOption = $_POST["searchOption"];
  $GT_id        = trim($_POST["GT_id"]);
  
  $entity_id    = str_replace("\r", " ", $entity_id);
	$entity_id    = str_replace("\n", "", $entity_id); 
  $entity_idSpl = explode(" ", $entity_id);
  $deltaTime    = 0;
  if ($GT_id === "")
  {
    $GT_id = $gtPlaceholder;
  }
  
  $execTime = time();
  
  $inputFileName      = "/tmp/pixelCablingIds_".$execTime.".dat";
  $outputXMLFileName  = "/tmp/pixelTrackerMap_".$execTime.".xml";
  $fedDBInfoFile      = "/tmp/dbCablData_".$GT_id.".dat";
  
  exec("echo > $inputFileName"); // create empty input file for Pixel Tracker Map Builder
  for ($i = 0; $i < count($entity_idSpl); $i++)
  {
    if ($entity_idSpl[$i] != "" && $entity_idSpl[$i] != " ")
    {
      // right now only static (one) color is allowed
      exec("echo '$entity_idSpl[$i] 255 0 0' >> $inputFileName"); // append (>>) to the file 
    }
  } 
  $output = shell_exec("python ConcatScript.py > DATA/CablingDB/pxCabl.csv 2>&1");
  // echo "<pre>$output</pre>";

  // THIS IS ONLY SEEMS LIKE A REAL SOLUTION BUT OTHERWISE WE ARE NOT ABLE TO ACCESS NEWER GTs, SO THE ONLY SOLUTION FOR THIS $#@%#@$%@ ARE STATIC CABLING FILES
  
  if (file_exists($fedDBInfoFile)) # PREVENTS FROM HUGE LOADING TIMES, DB file is going to be updated after an hour
  {
    $fileChangedTime = filemtime($fedDBInfoFile);
    $deltaTime = time() - $fileChangedTime;
    
    if ($deltaTime > $intervalBetweenReproduction)
    {     
      $output = shell_exec("bash runCMSSW.sh $GT_id> $fedDBInfoFile");
      // echo "<pre>$output</pre>";
    }
  }
  else{
    echo "<pre>Creating new file...</pre>";
    $output = shell_exec("bash runCMSSW.sh $GT_id> $fedDBInfoFile");
  } 
  
  // NOW CREATE XML FILE
  
  $output = shell_exec("python PixelTrackerMap.py $inputFileName $fedDBInfoFile $searchOption $outDicTxtFileName > $outputXMLFileName 2>&1");
  // echo "<pre>$output</pre>";
  
  // //  TEMPORAL SOLUTION - PRECREATED FED CABLING FILE WITH A GT THAT WAS UNREACHABLE ON AFS
  //$fedDBInfoFile = "DATA/CablingDB/pixelFEDCablingInfo.dat";
  //$fedDBInfoFile = "DATA/CablingDB/test.dat";
  //$output = shell_exec("python PixelTrackerMap.py $inputFileName $fedDBInfoFile $searchOption $outDicTxtFileName > $outputXMLFileName 2>&1");
}
?>

<link rel="stylesheet" href="https://unpkg.com/purecss@0.6.2/build/pure-min.css" integrity="sha384-UQiGfs9ICog+LwheBSRCt1o5cbyKIHbwjWscjemyBMT9YCUMZffs6UqUTd0hObXD" crossorigin="anonymous">
<link rel="stylesheet" href="DATA/main.css">
<meta name="viewport" content="width=device-width, initial-scale=1">

<div class="pure-g">
  <div class= "pure-u-1-5">
    <div class="l-box">
      <img style="height: 4em;" src="http://radecs2017.vitalis-events.com/wp-content/uploads/2016/09/CERN_logo2.svg_.png"/>
    </div>
  </div>
  <div class= "pure-u-3-5">
    <div class="l-box">
      <h1> Pixel Cabling Viewer </h1>
    </div>
  </div>
    <div class= "pure-u-1-5">
    <div class="l-box">
      <img style="position: absolute; right: 1em; height: 4em" src="https://cms-docdb.cern.ch/cgi-bin/PublicDocDB/RetrieveFile?docid=3045&filename=CMSlogo_black_label_1024_May2014.png&version=3"/>
    </div>
  </div>
</div>

<div class="pure-g">
  <div class="pure-u-1-6">
    <div class="l-box">
      <form class="pure-form" enctype = "multipart/form-data" action = "pixelcablingweb.php" method = "POST">
        <legend>Insert Pixel IDs to be marked:</legend>
        
        <label for="option-two" class="pure-radio">
          <input id="option-two" type="radio" name="searchOption" value="rawid" <?php if ($searchOption == "rawid") echo "checked" ?> >
            Det ID
        </label>
        
        <label for="option-three" class="pure-radio">
            <input id="option-three" type="radio" name="searchOption" value="fedid" <?php if ($searchOption == "fedid") echo "checked" ?>>
            FED ID [+CH/CH/...]
        </label>
        
        <label for="option-four" class="pure-radio">
            <input id="option-four" type="radio" name="searchOption" value="sectorid" <?php if ($searchOption == "sectorid") echo "checked" ?>>
            Barrel Sector
        </label>

        <label for="option-four" class="pure-radio">
            <input id="option-four" type="radio" name="searchOption" value="pcport" <?php if ($searchOption == "pcport") echo "checked" ?>>
           PC Port
        </label>

        <label for="option-four" class="pure-radio">
            <input id="option-four" type="radio" name="searchOption" value="pcid" <?php if ($searchOption == "pcid") echo "checked" ?>>
           PC ID
        </label>
        
        <textarea name="entity_id"  placeholder="353309700" style="font-size: 13px;"><?php echo $entity_id ?></textarea>

        <label for="GT_id" style="margin-top: 5px; display: block;">Global Tag:</label>
        <input name="GT_id" id="GT_id" style="width: 100%; margin: 5px 0px; font-size: 13px; text-align: center;" <?php echo "placeholder=\"".$gtPlaceholder."\" value=\"".$GT_id."\""; ?>>
        
        <button class="pure-button pure-button-primary" name="getCabling" type="submit" value="GetCabling" style="width: 100%">Get Cabling</button>
      </form>
      <?php
        echo "<p style=\"text-align: right;\">Delta time: ".$deltaTime." s</p>";
      ?>
    </div>
  </div>
  
  <div class="pure-u-5-6" style="transform: scale(1.0); transform-origin: 0 0;">
    <div class="l-box" style="text-align: center;">
      <?php 
        if (isset($_POST["getCabling"]))
        {
          // echo "<div style=\"transform: scale(0.7);\">";
          echo "<embed src='render_cabling_web_from_tmp.php?file=$outputXMLFileName&contenttype=text/xml'>";
          // echo "<iframe src='txt_cablingweb_from_tmp.php?file=$outDicTxtFileName'></iframe>";
          // echo "</div>";
        }
      ?>
    <div>
  </div>
  <div class="pure-u-1-1">
    <?php
      if (isset($_POST["getCabling"]))
      {
        echo "<a href='render_cabling_web_from_tmp.php?file=$outDicTxtFileName&contenttype=text/plain'>Link to the cabling info file </a>";
      }
    ?>
  </div>
</div>