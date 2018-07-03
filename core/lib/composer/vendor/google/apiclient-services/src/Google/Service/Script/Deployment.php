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

class Google_Service_Script_Deployment extends Google_Collection
{
  protected $collection_key = 'entryPoints';
  protected $deploymentConfigType = 'Google_Service_Script_DeploymentConfig';
  protected $deploymentConfigDataType = '';
  public $deploymentId;
  protected $entryPointsType = 'Google_Service_Script_EntryPoint';
  protected $entryPointsDataType = 'array';
  protected $functionSetType = 'Google_Service_Script_GoogleAppsScriptTypeFunctionSet';
  protected $functionSetDataType = '';
  protected $scopeSetType = 'Google_Service_Script_GoogleAppsScriptTypeScopeSet';
  protected $scopeSetDataType = '';
  public $updateTime;

  /**
   * @param Google_Service_Script_DeploymentConfig
   */
  public function setDeploymentConfig(Google_Service_Script_DeploymentConfig $deploymentConfig)
  {
    $this->deploymentConfig = $deploymentConfig;
  }
  /**
   * @return Google_Service_Script_DeploymentConfig
   */
  public function getDeploymentConfig()
  {
    return $this->deploymentConfig;
  }
  public function setDeploymentId($deploymentId)
  {
    $this->deploymentId = $deploymentId;
  }
  public function getDeploymentId()
  {
    return $this->deploymentId;
  }
  /**
   * @param Google_Service_Script_EntryPoint
   */
  public function setEntryPoints($entryPoints)
  {
    $this->entryPoints = $entryPoints;
  }
  /**
   * @return Google_Service_Script_EntryPoint
   */
  public function getEntryPoints()
  {
    return $this->entryPoints;
  }
  /**
   * @param Google_Service_Script_GoogleAppsScriptTypeFunctionSet
   */
  public function setFunctionSet(Google_Service_Script_GoogleAppsScriptTypeFunctionSet $functionSet)
  {
    $this->functionSet = $functionSet;
  }
  /**
   * @return Google_Service_Script_GoogleAppsScriptTypeFunctionSet
   */
  public function getFunctionSet()
  {
    return $this->functionSet;
  }
  /**
   * @param Google_Service_Script_GoogleAppsScriptTypeScopeSet
   */
  public function setScopeSet(Google_Service_Script_GoogleAppsScriptTypeScopeSet $scopeSet)
  {
    $this->scopeSet = $scopeSet;
  }
  /**
   * @return Google_Service_Script_GoogleAppsScriptTypeScopeSet
   */
  public function getScopeSet()
  {
    return $this->scopeSet;
  }
  public function setUpdateTime($updateTime)
  {
    $this->updateTime = $updateTime;
  }
  public function getUpdateTime()
  {
    return $this->updateTime;
  }
}
