Pixel Tracker Map
=================

This is a web interface to show Pixel Detector Cabling. You can filter Pixel Phase 1 detector modules providing the right ```< id >```:
1.  Det ID (rawID/onlineName), 353309700/BPix_BpI_SEC4_LYR1_LDR3F_MOD3
2.  FED ID (with Channel), 1243+37/13
3.  Barrel Sector
4.  Half Shell
5.  PC Port
6.  PC ID

You can provide as many inputs as you need (but they need to be of the same ```< id >``` category and in the case of the module ID all input values must be either rawID or onlineName - mixed types will not work). Inputs can be provided by hand - typing them directly into the list - or loaded from the user's file (in the latter case they will be put inside the list just after the click on the ```Get Cabling``` button). The tool will show you positions of modules having different ```< id >``` using different (random) colors. 

To see the all available information about any module just hover over it - the information will show up in the tooltip.

# More advanced usage

The cabling may change with time so the user has the possibility to specify the needed ```Global Tag```. However this tag has to be known by the current inside-the-tool installation of CMSSW (See maintenance section).

There is also the possibility to map values to the associated ```< id >```. If the user posseses a text file in the form ```<id> <value>``` (each entry in a separate row) and checks ```Mapping mode``` the result will be a map of values inside the Pixel Detector. The colors on the map will no longer be random. Instead a color scale is generated which will also be visible in the GUI (to the left of the map).

# Maintenance

The code requires a bit of knowledge of:
* php,
* python,
* css,
* javascript.

When the user clicks ```Get cabling``` button the tool determines whether it should rebuild cabling dictionary or not. The decision is made upon whether dictionary for a given Global Tag exists and if so how old it is. To (re)build cabling dictionary custom made CMSSW plugin is run. The CMSSW environment is set in ```runCMSSW.sh```. If you need to use more recent Global Tags you should first:
1. Make a new CMSSW installation.
2. Copy ```SiPixelPhase1CablingAnalyzer``` package to the new installation.
3. Build ```SiPixelPhase1CablingAnalyzer``` in the new place.
4. Edit ```runCMSSW.sh``` so that it points to the new CMSSW installation.

When the cabling dictionary is ready it is given as the input (along with user specified ```< ids >```) to the ```PixelTrackerMap.py``` and then Pixel Cabling Map is created as an SVG graphics.

# Final notes
If the tool was working and nobody touched it but now it does not produce Cabling Maps it is very probable that the web server is not able to run external scripts. With that kind of problem call [Viktor.Veszpremi@cern.ch](Viktor.Veszpremi@cern.ch).
