<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

class Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4 extends Google_Collection
{
  protected $collection_key = 'snapshots';
  public $captchaResult;
  protected $formattedResultsType = 'Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4FormattedResults';
  protected $formattedResultsDataType = '';
  public $id;
  public $invalidRules;
  public $kind;
  protected $loadingExperienceType = 'Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4LoadingExperience';
  protected $loadingExperienceDataType = '';
  protected $pageStatsType = 'Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4PageStats';
  protected $pageStatsDataType = '';
  public $responseCode;
  protected $ruleGroupsType = 'Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4RuleGroupsElement';
  protected $ruleGroupsDataType = 'map';
  protected $screenshotType = 'Google_Service_Pagespeedonline_PagespeedApiImageV4';
  protected $screenshotDataType = '';
  protected $snapshotsType = 'Google_Service_Pagespeedonline_PagespeedApiImageV4';
  protected $snapshotsDataType = 'array';
  public $title;
  protected $versionType = 'Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4Version';
  protected $versionDataType = '';

  public function setCaptchaResult($captchaResult)
  {
    $this->captchaResult = $captchaResult;
  }
  public function getCaptchaResult()
  {
    return $this->captchaResult;
  }
  /**
   * @param Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4FormattedResults
   */
  public function setFormattedResults(Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4FormattedResults $formattedResults)
  {
    $this->formattedResults = $formattedResults;
  }
  /**
   * @return Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4FormattedResults
   */
  public function getFormattedResults()
  {
    return $this->formattedResults;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setInvalidRules($invalidRules)
  {
    $this->invalidRules = $invalidRules;
  }
  public function getInvalidRules()
  {
    return $this->invalidRules;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  /**
   * @param Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4LoadingExperience
   */
  public function setLoadingExperience(Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4LoadingExperience $loadingExperience)
  {
    $this->loadingExperience = $loadingExperience;
  }
  /**
   * @return Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4LoadingExperience
   */
  public function getLoadingExperience()
  {
    return $this->loadingExperience;
  }
  /**
   * @param Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4PageStats
   */
  public function setPageStats(Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4PageStats $pageStats)
  {
    $this->pageStats = $pageStats;
  }
  /**
   * @return Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4PageStats
   */
  public function getPageStats()
  {
    return $this->pageStats;
  }
  public function setResponseCode($responseCode)
  {
    $this->responseCode = $responseCode;
  }
  public function getResponseCode()
  {
    return $this->responseCode;
  }
  /**
   * @param Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4RuleGroupsElement
   */
  public function setRuleGroups($ruleGroups)
  {
    $this->ruleGroups = $ruleGroups;
  }
  /**
   * @return Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4RuleGroupsElement
   */
  public function getRuleGroups()
  {
    return $this->ruleGroups;
  }
  /**
   * @param Google_Service_Pagespeedonline_PagespeedApiImageV4
   */
  public function setScreenshot(Google_Service_Pagespeedonline_PagespeedApiImageV4 $screenshot)
  {
    $this->screenshot = $screenshot;
  }
  /**
   * @return Google_Service_Pagespeedonline_PagespeedApiImageV4
   */
  public function getScreenshot()
  {
    return $this->screenshot;
  }
  /**
   * @param Google_Service_Pagespeedonline_PagespeedApiImageV4
   */
  public function setSnapshots($snapshots)
  {
    $this->snapshots = $snapshots;
  }
  /**
   * @return Google_Service_Pagespeedonline_PagespeedApiImageV4
   */
  public function getSnapshots()
  {
    return $this->snapshots;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
  /**
   * @param Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4Version
   */
  public function setVersion(Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4Version $version)
  {
    $this->version = $version;
  }
  /**
   * @return Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4Version
   */
  public function getVersion()
  {
    return $this->version;
  }
}
