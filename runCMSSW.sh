#!/bin/bash

# cmsenv

CMSSW_BASE=CMSSW_10_0_0
# CMSSW_BASE=CMSSW_9_3_0_pre4

source /cvmfs/cms.cern.ch/cmsset_default.sh
export SCRAM_ARCH=slc6_amd64_gcc700

# echo $SCRAM_ARCH
# echo $HOSTNAME

cd $CMSSW_BASE/src
# cd /cvmfs/cms.cern.ch/slc7_amd64_gcc700/cms/cmssw/$CMSSW_BASE/src

eval `scramv1 runtime -sh`
# echo $SCRAM_ARCH
# echo $LD_LIBRARY_PATH
# echo $OLDPWD
cd $OLDPWD

GT=$1
# GT=92X_upgrade2017_realistic_v11
RUN_COMMAND="cmsRun ${CMSSW_BASE}/src/DQM/SiPixelPhase1CablingAnalyzer/python/ConfFile_cfg.py globalTag=$GT"

$RUN_COMMAND
