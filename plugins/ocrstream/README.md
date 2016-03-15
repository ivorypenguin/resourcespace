# OCRstream

OCRstream is a plugin for the [ResourceSpace](http://resourcespace.org/) Digital Asset Management to integrate Optical Character Recognition through tesseract-ocr.

*OCRstream is still under development and should be considered experimental. If you plan to use it in production it is highly advised to install it in a test environment first.*

## Requirements

* ResourceSpace
* ImageMagick
* PHP 5.3+
* Tesseract OCR 3.x

Tesseract OCR needs to be installed for the plugin to work. See the Wiki [Links](https://github.com/andirotter/ocrstream/wiki/Links) for places to find downloads and/or instructions to build tesseract-ocr.  

## Installation

Download all files as a ZIP, unpack, rename folder to "ocrstream" and move it into your plugins directory of your RS installation.
Alternatively you can tar and gzip all files (.tar.gz) then rename the package to "ocrstream.rsp" and upload it via the plugin manager.
Once the plugin appears in you list of plugins you need to activate it.

## Configuration

It is important to go to the plugin "options" page right after activation.
You have to set the path to tesseract-ocr manually. Save the configuration and reload the page after that.

![Setup](https://cloud.githubusercontent.com/assets/3525191/11685971/6210693a-9e7d-11e5-911b-1e341d974ad2.png)

There is a default resource field set for the extracted text ("Extracted text") which means all recognized text of a resource will be written to it. You should consider changing that field according to you needs. Keep in mind that the default field will be indexed by RS meaning all extracted content will become indexed keywords. You might want to create a new custom field (e.g. "OCR text") which is not indexed and select that to write the content to it if you first want to check out the results of OCRstream before populating you DB with new keywords.

[...]

## Usage

There are three modes for OCRStream to operate in RS
* Single resource edit - when editing a resource you can do complete or partial OCR processing with custom settings
* Upload - when uploading resources you can select to do OCR processing for each resource after the upload completes with custom settings
* ~~Cronjob - you can set a custom "ocronjob" field to put resources in queue for OCR processing with default (global) settings. If you configured the RS cronjob properly ocronjob will be executed too.~~ *Not properly implemented yet*

[...]



