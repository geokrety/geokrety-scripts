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
  OutFile "confluence.exe"

  ;Default installation folder
  InstallDir "C:\Garmin\confluence"
  
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

File *.img
File *.TDB
File *.TYP

WriteRegBin HKLM "SOFTWARE\Garmin\MapSource\Families\confluence" 'ID' '009a'
WriteRegStr HKLM "SOFTWARE\Garmin\MapSource\Families\confluence" 'TYP' '$INSTDIR\conf.typ'
WriteRegStr HKLM "SOFTWARE\Garmin\MapSource\Families\confluence\1" 'LOC' '$INSTDIR'
WriteRegStr HKLM "SOFTWARE\Garmin\MapSource\Families\confluence\1" 'BMAP' '$INSTDIR\confluence.img'
WriteRegStr HKLM "SOFTWARE\Garmin\MapSource\Families\confluence\1" 'TDB' '$INSTDIR\confluence.TDB'

SectionEnd
