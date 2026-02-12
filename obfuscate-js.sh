#!/bin/bash
gulp --eprod
gulp pro-admin-js --eprod
gulp pro-modules-js --eprod

cd extensions/editor/user/web/js/quiz
npm install
npm run build
cd ../../../../../..
gulp ejs --xeditor/user --eprod
