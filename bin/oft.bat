@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/oft/fwk/bin/oft
php "%BIN_TARGET%" %*
