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

class Google_Service_Pagespeedonline_PagespeedApiPagespeedResponseV4PageStats extends Google_Collection
{
  protected $collection_key = 'transientFetchFailureUrls';
  public $cms;
  public $cssResponseBytes;
  public $flashResponseBytes;
  public $htmlResponseBytes;
  public $imageResponseBytes;
  public $javascriptResponseBytes;
  public $numRenderBlockingRoundTrips;
  public $numTotalRoundTrips;
  public $numberCssResources;
  public $numberHosts;
  public $numberJsResources;
  public $numberResources;
  public $numberRobotedResources;
  public $numberStaticResources;
  public $numberTransientFetchFailureResources;
  public $otherResponseBytes;
  public $overTheWireResponseBytes;
  public $robotedUrls;
  public $textResponseBytes;
  public $totalRequestBytes;
  public $transientFetchFailureUrls;

  public function setCms($cms)
  {
    $this->cms = $cms;
  }
  public function getCms()
  {
    return $this->cms;
  }
  public function setCssResponseBytes($cssResponseBytes)
  {
    $this->cssResponseBytes = $cssResponseBytes;
  }
  public function getCssResponseBytes()
  {
    return $this->cssResponseBytes;
  }
  public function setFlashResponseBytes($flashResponseBytes)
  {
    $this->flashResponseBytes = $flashResponseBytes;
  }
  public function getFlashResponseBytes()
  {
    return $this->flashResponseBytes;
  }
  public function setHtmlResponseBytes($htmlResponseBytes)
  {
    $this->htmlResponseBytes = $htmlResponseBytes;
  }
  public function getHtmlResponseBytes()
  {
    return $this->htmlResponseBytes;
  }
  public function setImageResponseBytes($imageResponseBytes)
  {
    $this->imageResponseBytes = $imageResponseBytes;
  }
  public function getImageResponseBytes()
  {
    return $this->imageResponseBytes;
  }
  public function setJavascriptResponseBytes($javascriptResponseBytes)
  {
    $this->javascriptResponseBytes = $javascriptResponseBytes;
  }
  public function getJavascriptResponseBytes()
  {
    return $this->javascriptResponseBytes;
  }
  public function setNumRenderBlockingRoundTrips($numRenderBlockingRoundTrips)
  {
    $this->numRenderBlockingRoundTrips = $numRenderBlockingRoundTrips;
  }
  public function getNumRenderBlockingRoundTrips()
  {
    return $this->numRenderBlockingRoundTrips;
  }
  public function setNumTotalRoundTrips($numTotalRoundTrips)
  {
    $this->numTotalRoundTrips = $numTotalRoundTrips;
  }
  public function getNumTotalRoundTrips()
  {
    return $this->numTotalRoundTrips;
  }
  public function setNumberCssResources($numberCssResources)
  {
    $this->numberCssResources = $numberCssResources;
  }
  public function getNumberCssResources()
  {
    return $this->numberCssResources;
  }
  public function setNumberHosts($numberHosts)
  {
    $this->numberHosts = $numberHosts;
  }
  public function getNumberHosts()
  {
    return $this->numberHosts;
  }
  public function setNumberJsResources($numberJsResources)
  {
    $this->numberJsResources = $numberJsResources;
  }
  public function getNumberJsResources()
  {
    return $this->numberJsResources;
  }
  public function setNumberResources($numberResources)
  {
    $this->numberResources = $numberResources;
  }
  public function getNumberResources()
  {
    return $this->numberResources;
  }
  public function setNumberRobotedResources($numberRobotedResources)
  {
    $this->numberRobotedResources = $numberRobotedResources;
  }
  public function getNumberRobotedResources()
  {
    return $this->numberRobotedResources;
  }
  public function setNumberStaticResources($numberStaticResources)
  {
    $this->numberStaticResources = $numberStaticResources;
  }
  public function getNumberStaticResources()
  {
    return $this->numberStaticResources;
  }
  public function setNumberTransientFetchFailureResources($numberTransientFetchFailureResources)
  {
    $this->numberTransientFetchFailureResources = $numberTransientFetchFailureResources;
  }
  public function getNumberTransientFetchFailureResources()
  {
    return $this->numberTransientFetchFailureResources;
  }
  public function setOtherResponseBytes($otherResponseBytes)
  {
    $this->otherResponseBytes = $otherResponseBytes;
  }
  public function getOtherResponseBytes()
  {
    return $this->otherResponseBytes;
  }
  public function setOverTheWireResponseBytes($overTheWireResponseBytes)
  {
    $this->overTheWireResponseBytes = $overTheWireResponseBytes;
  }
  public function getOverTheWireResponseBytes()
  {
    return $this->overTheWireResponseBytes;
  }
  public function setRobotedUrls($robotedUrls)
  {
    $this->robotedUrls = $robotedUrls;
  }
  public function getRobotedUrls()
  {
    return $this->robotedUrls;
  }
  public function setTextResponseBytes($textResponseBytes)
  {
    $this->textResponseBytes = $textResponseBytes;
  }
  public function getTextResponseBytes()
  {
    return $this->textResponseBytes;
  }
  public function setTotalRequestBytes($totalRequestBytes)
  {
    $this->totalRequestBytes = $totalRequestBytes;
  }
  public function getTotalRequestBytes()
  {
    return $this->totalRequestBytes;
  }
  public function setTransientFetchFailureUrls($transientFetchFailureUrls)
  {
    $this->transientFetchFailureUrls = $transientFetchFailureUrls;
  }
  public function getTransientFetchFailureUrls()
  {
    return $this->transientFetchFailureUrls;
  }
}
