<?php

header("Content-type: text/html");

$execOut = "";
$execErr = "";

$cablingTxt = "";
$entity_id = "";

$searchOption = "rawid";
$outDicTxtFileName = "/tmp/pixelcablingweb_cablingInfo.dat";

$intervalBetweenReproduction = 3600 * 4;

$gtPlaceholder = "92X_upgrade2017_realistic_v11";

// MIN MAX FIND
$currMin = 9999999999.0;
$currMax = -9999999999.0;


// PALETTE BUILDING
function GetPaletteColorForValue($val, $currMin, $currMax)
{
  // echo $val." ";
  $pos = ($val - $currMin) / ($currMax - $currMin);

  $R = 0;
  $G = 0;
  $B = 0;

  // echo $pos."\t";

  if ($pos < 0.5)
  {
    $pos = $pos * 2.0;
    $B = intval((1.0 - $pos) * 255);
    $G = intval($pos * 255);
  }
  else
  {
    $pos = ($pos - 0.5) * 2.0;
    $R = intval($pos * 255);
    $G = intval((1.0 - $pos) * 255);
  }
  return $R." ".$G." ".$B;
}

if (isset($_POST["getCabling"]))
{
  $cablingTxt   = $_POST["getCabling"];
  $searchOption = $_POST["searchOption"];
  $mapOption    = $_POST["mapOption"];
  $GT_id        = trim($_POST["GT_id"]);

  $execTime = time();
  
  $inputFileName      = "/tmp/pixelCablingIds_".$execTime.".dat";
  $outputXMLFileName  = "/tmp/pixelTrackerMap_".$execTime.".xml";
  $fedDBInfoFile      = "/tmp/dbCablData_".$GT_id.".dat";
  $useRandomBinColors = "1";
   
  exec("echo > $inputFileName"); // create empty input file for Pixel Tracker Map Builder

  // echo $inputFileName."\n";

  if ($_FILES["fileToUpload"]["tmp_name"] === "")
  {
    $entity_id = $_POST["entity_id"];
  }
  else
  {
    $entity_id = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);
  }
  

  $entity_idSpl;
  if ($mapOption === "true")
  {
    $entity_idSpl = explode("\n", $entity_id);

    for ($i = 0; $i < count($entity_idSpl); $i++)
    {
      // if (strlen(trim(" ", $entity_idSpl[$i])) == 0) continue;

      $lineSpl = explode(" ", trim($entity_idSpl[$i]));

      $currVal = floatval($lineSpl[1]);

      if ($currVal > $currMax){
        $currMax = $currVal;
      }
      if ($currVal < $currMin){
        $currMin = $currVal;
      }
    }

    if ($currMin == $currMax)
    {
      $currMin = $currMin - 1.0;
    }

    // echo "MIN/MAX: ".$currMin."/".$currMax." ";
    // echo "2ND PASS...";

    // SECOND PASS
    for ($i = 0; $i < count($entity_idSpl); $i++)
    {
      // if (strlen(trim(" ", $entity_idSpl[$i])) == 0) continue;

      $currStr = str_replace("\r", "", $entity_idSpl[$i]);
      $lineSpl = explode(" ", trim($currStr));

      $generalID = $lineSpl[0];
      $inputVal = floatval($lineSpl[1]);

      $RGB = GetPaletteColorForValue($inputVal, $currMin, $currMax);
      exec("echo '$generalID $RGB' >> $inputFileName"); // append (>>) to the file
    }
    $useRandomBinColors = "0";
  }
  else{
    $entity_id    = str_replace("\r", " ", $entity_id);
    $entity_id    = str_replace("\n", "", $entity_id);
    $entity_idSpl = explode(" ", $entity_id);

    for ($i = 0; $i < count($entity_idSpl); $i++)
    {
      if ($entity_idSpl[$i] != "" && $entity_idSpl[$i] != " ")
      {
        // right now only static (one) color is allowed
        exec("echo '$entity_idSpl[$i] 100 100 100' >> $inputFileName"); // append (>>) to the file 
      }
    } 
  }
  
  if ($GT_id === "")
  {
    $GT_id = $gtPlaceholder;
  }
  
  $output = shell_exec("python ConcatScript.py > DATA/CablingDB/pxCabl.csv 2>&1");
  // echo "<pre>$output</pre>";

  // THIS IS ONLY SEEMS LIKE A REAL SOLUTION BUT OTHERWISE WE ARE NOT ABLE TO ACCESS NEWER GTs, SO THE ONLY SOLUTION FOR THIS $#@%#@$%@ ARE STATIC CABLING FILES
  $deltaTime    = 0;
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
  
  $output = shell_exec("python PixelTrackerMap.py $inputFileName $fedDBInfoFile $searchOption $outDicTxtFileName $useRandomBinColors > $outputXMLFileName 2>&1");
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
        <!-- <legend>Insert Pixel IDs to be marked:</legend> -->

        <label for="idSelect">Generic ID type:</label>
        <select id="idSelect" name="searchOption" style="width: 100%;">
          <option value="rawid" <?php if ($searchOption == "rawid") echo "selected" ?>>Det ID (raw or online)</option>
          <option value="fedid" <?php if ($searchOption == "fedid") echo "selected" ?>>FED ID [+CH/CH/...]</option>
          <option value="sectorid" <?php if ($searchOption == "sectorid") echo "selected" ?>>Barrel Sector</option>
          <option value="halfshellid" <?php if ($searchOption == "halfshellid") echo "selected" ?>>Half Shell</option>
          <option value="pcport" <?php if ($searchOption == "pcport") echo "selected" ?>>PC Port</option>
          <option value="pcid" <?php if ($searchOption == "pcid") echo "selected" ?>>PC ID</option>
        </select>

        <label for="option-mapping" class="pure-radio" style="text-align: right;">
            <input id="option-mapping" type="checkbox" name="mapOption" value="true" <?php if ($mapOption == "true") echo "checked" ?>>
           Mapping mode
        </label>
        
        <label for="idEntries">List of entries:</label>
        <textarea id="idEntries" name="entity_id"  placeholder="353309700" style="font-size: 13px;"><?php echo $entity_id ?></textarea>
        <input type="file" name="fileToUpload" id="fileToUpload">

        <label for="GT_id" style="margin-top: 5px; display: block;">Global Tag:</label>
        <input name="GT_id" id="GT_id" style="width: 100%; margin: 5px 0px; font-size: 13px; text-align: center;" <?php echo "placeholder=\"".$gtPlaceholder."\" value=\"".$GT_id."\""; ?>>
        
        <button class="pure-button pure-button-primary" name="getCabling" type="submit" value="GetCabling" style="width: 100%">Get Cabling</button>
      </form>

      <?php
        echo "<p style=\"text-align: right;\">Delta time: ".$deltaTime." s</p>";
      ?>
    </div>
  </div>

  <div class="pure-u-1-24" >
    <div id="colorScale" style="
    background: rgb(231,56,39);
    background: -moz-linear-gradient(top, rgb(231,56,39) 0%, rgb(0,255,0) 50%, rgb(0,0,255) 100%);
    background: -webkit-gradient(left top, left bottom, color-stop(0%, rgb(231,56,39)), color-stop(50%, rgb(0,255,0)), color-stop(100%, rgb(0,0,255)));
    background: -webkit-linear-gradient(top, rgb(231,56,39) 0%, rgb(0,255,0) 50%, rgb(0,0,255) 100%);
    background: -o-linear-gradient(top, rgb(231,56,39) 0%, rgb(0,255,0) 50%, rgb(0,0,255) 100%);
    background: -ms-linear-gradient(top, rgb(231,56,39) 0%, rgb(0,255,0) 50%, rgb(0,0,255) 100%);
    background: linear-gradient(to bottom, rgb(231,56,39) 0%, rgb(0,255,0) 50%, rgb(0,0,255) 100%);
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#e73827', endColorstr='#0000ff', GradientType=0 ); 
    text-align: center;
    border-radius: 6px;
    width: 66%;
    height: 500px;
    <?php if ($mapOption !== "true") echo "display: none;" ?>" 
    >
      <div style="position: relative;
                  left: 100%;
                  padding-top: 5px;">
        <div style="position: relative; top: 0px;"><?php echo sprintf("%.2f", $currMax); ?></div>
        <div style="position: relative; top: 100px;"><?php echo sprintf("%.2f", ($currMax - $currMin) * 0.75 + $currMin); ?></div>
        <div style="position: relative; top: 200px;"><?php echo sprintf("%.2f", ($currMax + $currMin) * 0.50); ?></div>
        <div style="position: relative; top: 300px;"><?php echo sprintf("%.2f", ($currMax - $currMin) * 0.25 + $currMin); ?></div>
        <div style="position: relative; top: 400px;"><?php echo sprintf("%.2f", $currMin); ?></div>
      </div>
    </div>
  </div>
  
  <div class="pure-u-19-24" style="transform: scale(1.0); transform-origin: 0 0;">
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