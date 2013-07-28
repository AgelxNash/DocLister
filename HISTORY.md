## 1.0.18 (28.07.2013)
* add new parameters JSONformat

## 1.0.17 (17.07.2013)
* fix whereTag method from site_content_tags controller

## 1.0.16 (11.06.2013)
* add new parameters tplFirst, tplLast, tplCurrent

## 1.0.15 (24.05.2013)
* Fix paginate template
* Freezing get documents method
* add new parameter noneWrapOuter

## 1.0.14 (08.05.2013)
* add current page placeholder into paginate extender

## 1.0.13 (04.05.2013)
* [Bug] Declaration getJSON() compability with DocLister::getJSON($data, $fields, $array = Array)
 
## 1.0.12 (2013-05-03)
* [Bug] ignore api parameter

## 1.0.11 (2013-04-28)
* [Bug] Set default templates in pagination with @CODE
* [Bug] parents parameter in cleanIDs with space

## 1.0.10 (2013-04-16)
* Delete synchronization DocLister::$_cfg['parents'] and DocLister::$IDs for check default parameters

## 1.0.9 (2013-03-28)
* new method for template on Doclister::getChunkByParam
* fix DocLister::_getChunk

## 1.0.8 (2013-03-26)
* New method for template on DocLister::_getChunk
* New extender template for easy add new custom method for template
* fix url for main page
* fix EOF

## 1.0.7 (2013-03-12)
* add method DocLister::checkDL() for control main parameter
* refactoring snippet DocLister

## 1.0.6 (2013-03-11)
* add parameter pageAdjacents and pageLimit for paginate extender
* try-catch in construct DocLister
* new parser template (new method DocLister::parseChunk())
* add filtering { and } in DocLister::sanitarData()
* refactor date placeholder

## 1.0.5 (2013-03-10)
* rename class extenders
* add new method DocLister::checkExtender() for check extender
* add usertype parameter for user extender
* add new controller site_content without Tag and rename site_content with tag to site_content_tags
* add new method DocLister::treeBuild() for build tree array
* test controller tree

## 1.0.4 (2013-03-09)
* add user extender
* add new controller onetable for show info from custom table
* add new method DocLister::getOneField
* add access to $modx of DocList extender
* add new action noparser for summary extender
* user user extender in all controller
* iteration placeholder start from 1

## 1.0.3 (2013-03-08)
* change default dateSource to pub_date in site_content controller
* add orderBy parameter. combine sortBy and sortDir
* add sortDir parameter. Synonym order parameter
* add fieldSort parameter. Default field sort

## 1.0.2 (2013-03-07)
* API mode refactor

## 1.0.1 (2013-03-06)
* Fix compatibility with PHP < 5.2.9

## 1.0.0 (2013-03-04)
* First release
