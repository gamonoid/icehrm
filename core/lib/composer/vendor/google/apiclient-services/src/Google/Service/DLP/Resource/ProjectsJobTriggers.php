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
 * The "jobTriggers" collection of methods.
 * Typical usage is:
 *  <code>
 *   $dlpService = new Google_Service_DLP(...);
 *   $jobTriggers = $dlpService->jobTriggers;
 *  </code>
 */
class Google_Service_DLP_Resource_ProjectsJobTriggers extends Google_Service_Resource
{
  /**
   * Creates a job trigger to run DLP actions such as scanning storage for
   * sensitive information on a set schedule. (jobTriggers.create)
   *
   * @param string $parent The parent resource name, for example projects/my-
   * project-id.
   * @param Google_Service_DLP_GooglePrivacyDlpV2CreateJobTriggerRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_DLP_GooglePrivacyDlpV2JobTrigger
   */
  public function create($parent, Google_Service_DLP_GooglePrivacyDlpV2CreateJobTriggerRequest $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_DLP_GooglePrivacyDlpV2JobTrigger");
  }
  /**
   * Deletes a job trigger. (jobTriggers.delete)
   *
   * @param string $name Resource name of the project and the triggeredJob, for
   * example `projects/dlp-test-project/jobTriggers/53234423`.
   * @param array $optParams Optional parameters.
   * @return Google_Service_DLP_GoogleProtobufEmpty
   */
  public function delete($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_DLP_GoogleProtobufEmpty");
  }
  /**
   * Gets a job trigger. (jobTriggers.get)
   *
   * @param string $name Resource name of the project and the triggeredJob, for
   * example `projects/dlp-test-project/jobTriggers/53234423`.
   * @param array $optParams Optional parameters.
   * @return Google_Service_DLP_GooglePrivacyDlpV2JobTrigger
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_DLP_GooglePrivacyDlpV2JobTrigger");
  }
  /**
   * Lists job triggers. (jobTriggers.listProjectsJobTriggers)
   *
   * @param string $parent The parent resource name, for example projects/my-
   * project-id.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string pageToken Optional page token to continue retrieval. Comes
   * from previous call to ListJobTriggers. `order_by` and `filter` should not
   * change for subsequent calls, but can be omitted if token is specified.
   * @opt_param string orderBy Optional comma separated list of triggeredJob
   * fields to order by, followed by 'asc/desc' postfix, i.e. `"create_time
   * asc,name desc,schedule_mode asc"`. This list is case-insensitive.
   *
   * Example: `"name asc,schedule_mode desc, status desc"`
   *
   * Supported filters keys and values are:
   *
   * - `create_time`: corresponds to time the triggeredJob was created. -
   * `update_time`: corresponds to time the triggeredJob was last updated. -
   * `name`: corresponds to JobTrigger's display name. - `status`: corresponds to
   * the triggeredJob status.
   * @opt_param int pageSize Optional size of the page, can be limited by a
   * server.
   * @return Google_Service_DLP_GooglePrivacyDlpV2ListJobTriggersResponse
   */
  public function listProjectsJobTriggers($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_DLP_GooglePrivacyDlpV2ListJobTriggersResponse");
  }
  /**
   * Updates a job trigger. (jobTriggers.patch)
   *
   * @param string $name Resource name of the project and the triggeredJob, for
   * example `projects/dlp-test-project/jobTriggers/53234423`.
   * @param Google_Service_DLP_GooglePrivacyDlpV2UpdateJobTriggerRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_DLP_GooglePrivacyDlpV2JobTrigger
   */
  public function patch($name, Google_Service_DLP_GooglePrivacyDlpV2UpdateJobTriggerRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_DLP_GooglePrivacyDlpV2JobTrigger");
  }
}
