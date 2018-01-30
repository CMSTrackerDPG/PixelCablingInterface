var PixelTrackerShow = {} ;
PixelTrackerShow.thisFile = "my.js";

var interestingQuantities = ["FEDID", "FEDposition", "FEDchannel", "PCname", "PCport", "PCidentifier"]

PixelTrackerShow.init = function()
{
  showData = PixelTrackerShow.showData;  
}

PixelTrackerShow.showData = function (evt) 
{
  var myPoly = evt.currentTarget;
  
  if (evt.type == "mouseover") 
  {
    var myPoly = evt.currentTarget;
    var id = myPoly.getAttribute("detId");
    var oid = myPoly.getAttribute("oid");
    var fedChannel = myPoly.getAttribute("FEDchannel");

    var textfield = document.getElementById("moduleName");
    textfield.firstChild.nodeValue = oid + " (" + id + ")";
    
    for (var i = 0; i < interestingQuantities.length; ++i)
    {
      var k = interestingQuantities[i];
      var s = myPoly.getAttribute(k);
      var nk = k + "_val";
      if (s == null) // Id not found in cabling DB
      { 
        // s = ""; 

        document.getElementById(k).style.visibility = "hidden";
        document.getElementById(nk).style.visibility = "hidden";
      }
      else
      {
        
        document.getElementById(nk).innerHTML = s;

        document.getElementById(k).style.visibility = "inherit";
        document.getElementById(nk).style.visibility = "inherit";
      }
    } 
  }
  ShowTooltip(evt);
  
  if (evt.type == "mouseout") 
  {  
    HideTooltip();
  }
}

function ShowTooltip(evt)
{
  var tooltip_bg = document.getElementById('tooltip_bg');
  var infoTable = document.getElementById('infoTable');
  
  var winWidth = window.innerWidth;
  var winHeight = window.innerHeight;
  
  var tooltipX = evt.pageX + 3;
  var tooltipY = evt.pageY;
  
  var tooltipWidth = 350;
  var tooltipHeight = 150;
  
  // make tooltip fit into its parent
  if (tooltipX + tooltipWidth >= winWidth)
  {
    tooltipX = evt.pageX - 3 - tooltipWidth;
  }
  if (tooltipY + tooltipHeight >= winHeight)
  {
    //tooltipY = evt.pageY - tooltipHeight + 20

    tooltipY = evt.pageY - (tooltipY + tooltipHeight - winHeight);
  }  
  
  tooltip_bg.setAttributeNS(null,"x",tooltipX);
	tooltip_bg.setAttributeNS(null,"y",tooltipY);
	tooltip_bg.setAttributeNS(null,"visibility","visible");
  tooltip_bg.setAttributeNS(null,"width",tooltipWidth);
  
  infoTable.setAttributeNS(null,"x",tooltipX);
	infoTable.setAttributeNS(null,"y",tooltipY);
	infoTable.setAttributeNS(null,"visibility","visible");
}
function HideTooltip()
{
  document.getElementById('tooltip_bg').setAttributeNS(null,"visibility","hidden");
  document.getElementById('infoTable').setAttributeNS(null,"visibility","hidden");
}
