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

class Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4FormattedResultsRuleResultsElement extends Google_Collection
{
  protected $collection_key = 'urlBlocks';
  public $beta;
  public $groups;
  public $localizedRuleName;
  public $ruleImpact;
  protected $summaryType = 'Google_Service_Pagespeedonline_PagespeedApiFormatStringV4';
  protected $summaryDataType = '';
  protected $urlBlocksType = 'Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4FormattedResultsRuleResultsElementUrlBlocks';
  protected $urlBlocksDataType = 'array';

  public function setBeta($beta)
  {
    $this->beta = $beta;
  }
  public function getBeta()
  {
    return $this->beta;
  }
  public function setGroups($groups)
  {
    $this->groups = $groups;
  }
  public function getGroups()
  {
    return $this->groups;
  }
  public function setLocalizedRuleName($localizedRuleName)
  {
    $this->localizedRuleName = $localizedRuleName;
  }
  public function getLocalizedRuleName()
  {
    return $this->localizedRuleName;
  }
  public function setRuleImpact($ruleImpact)
  {
    $this->ruleImpact = $ruleImpact;
  }
  public function getRuleImpact()
  {
    return $this->ruleImpact;
  }
  /**
   * @param Google_Service_Pagespeedonline_PagespeedApiFormatStringV4
   */
  public function setSummary(Google_Service_Pagespeedonline_PagespeedApiFormatStringV4 $summary)
  {
    $this->summary = $summary;
  }
  /**
   * @return Google_Service_Pagespeedonline_PagespeedApiFormatStringV4
   */
  public function getSummary()
  {
    return $this->summary;
  }
  /**
   * @param Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4FormattedResultsRuleResultsElementUrlBlocks
   */
  public function setUrlBlocks($urlBlocks)
  {
    $this->urlBlocks = $urlBlocks;
  }
  /**
   * @return Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4FormattedResultsRuleResultsElementUrlBlocks
   */
  public function getUrlBlocks()
  {
    return $this->urlBlocks;
  }
}
