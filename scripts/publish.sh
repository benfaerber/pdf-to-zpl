#!/usr/bin/env bash


echo "Attempting to publish $1 of pdf-to-zpl" 
git tag $1 
git push origin $1
open https://packagist.org/packages/faerber/pdf-to-zpl

