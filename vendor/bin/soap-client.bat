@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../phpro/soap-client/bin/soap-client
php "%BIN_TARGET%" %*
