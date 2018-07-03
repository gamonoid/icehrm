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
 * The "content" collection of methods.
 * Typical usage is:
 *  <code>
 *   $dlpService = new Google_Service_DLP(...);
 *   $content = $dlpService->content;
 *  </code>
 */
class Google_Service_DLP_Resource_ProjectsContent extends Google_Service_Resource
{
  /**
   * De-identifies potentially sensitive info from a ContentItem. This method has
   * limits on input size and output size. [How-to guide](/dlp/docs/deidentify-
   * sensitive-data) (content.deidentify)
   *
   * @param string $parent The parent resource name, for example projects/my-
   * project-id.
   * @param Google_Service_DLP_GooglePrivacyDlpV2DeidentifyContentRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_DLP_GooglePrivacyDlpV2DeidentifyContentResponse
   */
  public function deidentify($parent, Google_Service_DLP_GooglePrivacyDlpV2DeidentifyContentRequest $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('deidentify', array($params), "Google_Service_DLP_GooglePrivacyDlpV2DeidentifyContentResponse");
  }
  /**
   * Finds potentially sensitive info in content. This method has limits on input
   * size, processing time, and output size. [How-to guide for text](/dlp/docs
   * /inspecting-text), [How-to guide for images](/dlp/docs/inspecting-images)
   * (content.inspect)
   *
   * @param string $parent The parent resource name, for example projects/my-
   * project-id.
   * @param Google_Service_DLP_GooglePrivacyDlpV2InspectContentRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_DLP_GooglePrivacyDlpV2InspectContentResponse
   */
  public function inspect($parent, Google_Service_DLP_GooglePrivacyDlpV2InspectContentRequest $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('inspect', array($params), "Google_Service_DLP_GooglePrivacyDlpV2InspectContentResponse");
  }
  /**
   * Re-identifies content that has been de-identified. (content.reidentify)
   *
   * @param string $parent The parent resource name.
   * @param Google_Service_DLP_GooglePrivacyDlpV2ReidentifyContentRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_DLP_GooglePrivacyDlpV2ReidentifyContentResponse
   */
  public function reidentify($parent, Google_Service_DLP_GooglePrivacyDlpV2ReidentifyContentRequest $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('reidentify', array($params), "Google_Service_DLP_GooglePrivacyDlpV2ReidentifyContentResponse");
  }
}
