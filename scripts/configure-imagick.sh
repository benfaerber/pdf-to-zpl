#!/usr/bin/env bash

# We need to edit some security policies to allow the library to work with PDFs
# Run this script as admin to allow Image Magick to edit PDFs

image_magick_folder=$(find "/etc" -type d -name "ImageMagick-*" -print -quit)
policy_file="$image_magick_folder/policy.xml"
echo $policy_file;
sed -i 's@rights="none" pattern="PDF"@rights="read | write" pattern="PDF"@g' "$policy_file"

echo 'New Version of File:\n'
cat $policy_file

echo '\nChange Made:'
cat $policy_file | grep "pattern=\"PDF\""

echo '\nNo Errors Detected! You now should be able to modify PDFs'
