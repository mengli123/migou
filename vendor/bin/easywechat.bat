@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../easywechat/console/bin/easywechat
php "%BIN_TARGET%" %*
