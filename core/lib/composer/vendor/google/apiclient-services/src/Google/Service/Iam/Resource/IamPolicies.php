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

/**
 * The "iamPolicies" collection of methods.
 * Typical usage is:
 *  <code>
 *   $iamService = new Google_Service_Iam(...);
 *   $iamPolicies = $iamService->iamPolicies;
 *  </code>
 */
class Google_Service_Iam_Resource_IamPolicies extends Google_Service_Resource
{
  /**
   * Returns a list of services that support service level audit logging
   * configuration for the given resource. (iamPolicies.queryAuditableServices)
   *
   * @param Google_Service_Iam_QueryAuditableServicesRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Iam_QueryAuditableServicesResponse
   */
  public function queryAuditableServices(Google_Service_Iam_QueryAuditableServicesRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('queryAuditableServices', array($params), "Google_Service_Iam_QueryAuditableServicesResponse");
  }
}
