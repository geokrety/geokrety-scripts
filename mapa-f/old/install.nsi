;NSIS Modern User Interface
;Basic Example Script
;Written by Joost Verburg

;--------------------------------
;Include Modern UI

  !include "MUI2.nsh"

;--------------------------------
;General

  ;Name and file
  Name "Geocache map of the world by geokrety.org"
  OutFile "../out/geocaches.exe"

  ;Default installation folder
  InstallDir "C:\Garmin\geocaches"
  
  ;Request application privileges for Windows Vista
  RequestExecutionLevel user

;--------------------------------
;Interface Settings
  !define MUI_ABORTWARNING

;--------------------------------
;Pages
  !insertmacro MUI_PAGE_DIRECTORY
  !insertmacro MUI_PAGE_INSTFILES
  
  !insertmacro MUI_UNPAGE_CONFIRM
  !insertmacro MUI_UNPAGE_INSTFILES
  
;--------------------------------
;Languages
 
  !insertmacro MUI_LANGUAGE "English"

;--------------------------------
;Installer Sections

Section "Dummy Section" SecDummy
  SetOutPath "$INSTDIR"

File 60845003.img
File 60845005.img
File 60845006.img
File 60845007.img
File 60845008.img
File 60845009.img
File 60845012.img
File 60845013.img
File 60845014.img
File 60845015.img
File 60845016.img
File 60845017.img
File 60845018.img
File 60845019.img
File 60845020.img
File 60845021.img
File 60845022.img
File 60845023.img
File 60845024.img
File 60845025.img
File geocaches.img
File geocaches.TDB
File geocaches.MDX

  WriteRegStr HKLM "SOFTWARE\Garmin\MapSource\Products\geocaches" 'LOC' '$INSTDIR\'
  WriteRegStr HKLM "SOFTWARE\Garmin\MapSource\Products\geocaches" 'BMAP' '$INSTDIR\geocaches.img'
  WriteRegStr HKLM "SOFTWARE\Garmin\MapSource\Products\geocaches" 'TDB' '$INSTDIR\geocaches.tdb'
SectionEnd
