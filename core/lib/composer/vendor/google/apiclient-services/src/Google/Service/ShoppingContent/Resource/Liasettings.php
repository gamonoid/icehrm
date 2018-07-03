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
 * The "liasettings" collection of methods.
 * Typical usage is:
 *  <code>
 *   $contentService = new Google_Service_ShoppingContent(...);
 *   $liasettings = $contentService->liasettings;
 *  </code>
 */
class Google_Service_ShoppingContent_Resource_Liasettings extends Google_Service_Resource
{
  /**
   * Retrieves and/or updates the LIA settings of multiple accounts in a single
   * request. (liasettings.custombatch)
   *
   * @param Google_Service_ShoppingContent_LiasettingsCustomBatchRequest $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool dryRun Flag to run the request in dry-run mode.
   * @return Google_Service_ShoppingContent_LiasettingsCustomBatchResponse
   */
  public function custombatch(Google_Service_ShoppingContent_LiasettingsCustomBatchRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('custombatch', array($params), "Google_Service_ShoppingContent_LiasettingsCustomBatchResponse");
  }
  /**
   * Retrieves the LIA settings of the account. (liasettings.get)
   *
   * @param string $merchantId The ID of the managing account. If this parameter
   * is not the same as accountId, then this account must be a multi-client
   * account and accountId must be the ID of a sub-account of this account.
   * @param string $accountId The ID of the account for which to get or update LIA
   * settings.
   * @param array $optParams Optional parameters.
   * @return Google_Service_ShoppingContent_LiaSettings
   */
  public function get($merchantId, $accountId, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_ShoppingContent_LiaSettings");
  }
  /**
   * Retrieves the list of accessible Google My Business accounts.
   * (liasettings.getaccessiblegmbaccounts)
   *
   * @param string $merchantId The ID of the managing account. If this parameter
   * is not the same as accountId, then this account must be a multi-client
   * account and accountId must be the ID of a sub-account of this account.
   * @param string $accountId The ID of the account for which to retrieve
   * accessible Google My Business accounts.
   * @param array $optParams Optional parameters.
   * @return Google_Service_ShoppingContent_LiasettingsGetAccessibleGmbAccountsResponse
   */
  public function getaccessiblegmbaccounts($merchantId, $accountId, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('getaccessiblegmbaccounts', array($params), "Google_Service_ShoppingContent_LiasettingsGetAccessibleGmbAccountsResponse");
  }
  /**
   * Lists the LIA settings of the sub-accounts in your Merchant Center account.
   * (liasettings.listLiasettings)
   *
   * @param string $merchantId The ID of the managing account. This must be a
   * multi-client account.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string maxResults The maximum number of LIA settings to return in
   * the response, used for paging.
   * @opt_param string pageToken The token returned by the previous request.
   * @return Google_Service_ShoppingContent_LiasettingsListResponse
   */
  public function listLiasettings($merchantId, $optParams = array())
  {
    $params = array('merchantId' => $merchantId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_ShoppingContent_LiasettingsListResponse");
  }
  /**
   * Updates the LIA settings of the account. This method supports patch
   * semantics. (liasettings.patch)
   *
   * @param string $merchantId The ID of the managing account. If this parameter
   * is not the same as accountId, then this account must be a multi-client
   * account and accountId must be the ID of a sub-account of this account.
   * @param string $accountId The ID of the account for which to get or update LIA
   * settings.
   * @param Google_Service_ShoppingContent_LiaSettings $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool dryRun Flag to run the request in dry-run mode.
   * @return Google_Service_ShoppingContent_LiaSettings
   */
  public function patch($merchantId, $accountId, Google_Service_ShoppingContent_LiaSettings $postBody, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'accountId' => $accountId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_ShoppingContent_LiaSettings");
  }
  /**
   * Requests access to a specified Google My Business account.
   * (liasettings.requestgmbaccess)
   *
   * @param string $merchantId The ID of the managing account. If this parameter
   * is not the same as accountId, then this account must be a multi-client
   * account and accountId must be the ID of a sub-account of this account.
   * @param string $accountId The ID of the account for which GMB access is
   * requested.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string gmbEmail The email of the Google My Business account.
   * @return Google_Service_ShoppingContent_LiasettingsRequestGmbAccessResponse
   */
  public function requestgmbaccess($merchantId, $accountId, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('requestgmbaccess', array($params), "Google_Service_ShoppingContent_LiasettingsRequestGmbAccessResponse");
  }
  /**
   * Requests inventory validation for the specified country.
   * (liasettings.requestinventoryverification)
   *
   * @param string $merchantId The ID of the managing account. If this parameter
   * is not the same as accountId, then this account must be a multi-client
   * account and accountId must be the ID of a sub-account of this account.
   * @param string $accountId The ID of the account that manages the order. This
   * cannot be a multi-client account.
   * @param string $country The country for which inventory validation is
   * requested.
   * @param array $optParams Optional parameters.
   * @return Google_Service_ShoppingContent_LiasettingsRequestInventoryVerificationResponse
   */
  public function requestinventoryverification($merchantId, $accountId, $country, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'accountId' => $accountId, 'country' => $country);
    $params = array_merge($params, $optParams);
    return $this->call('requestinventoryverification', array($params), "Google_Service_ShoppingContent_LiasettingsRequestInventoryVerificationResponse");
  }
  /**
   * Sets the inventory verification contract for the specified country.
   * (liasettings.setinventoryverificationcontact)
   *
   * @param string $merchantId The ID of the managing account. If this parameter
   * is not the same as accountId, then this account must be a multi-client
   * account and accountId must be the ID of a sub-account of this account.
   * @param string $accountId The ID of the account that manages the order. This
   * cannot be a multi-client account.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string contactEmail The email of the inventory verification
   * contact.
   * @opt_param string contactName The name of the inventory verification contact.
   * @opt_param string country The country for which inventory verification is
   * requested.
   * @opt_param string language The language for which inventory verification is
   * requested.
   * @return Google_Service_ShoppingContent_LiasettingsSetInventoryVerificationContactResponse
   */
  public function setinventoryverificationcontact($merchantId, $accountId, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('setinventoryverificationcontact', array($params), "Google_Service_ShoppingContent_LiasettingsSetInventoryVerificationContactResponse");
  }
  /**
   * Updates the LIA settings of the account. (liasettings.update)
   *
   * @param string $merchantId The ID of the managing account. If this parameter
   * is not the same as accountId, then this account must be a multi-client
   * account and accountId must be the ID of a sub-account of this account.
   * @param string $accountId The ID of the account for which to get or update LIA
   * settings.
   * @param Google_Service_ShoppingContent_LiaSettings $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool dryRun Flag to run the request in dry-run mode.
   * @return Google_Service_ShoppingContent_LiaSettings
   */
  public function update($merchantId, $accountId, Google_Service_ShoppingContent_LiaSettings $postBody, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'accountId' => $accountId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_ShoppingContent_LiaSettings");
  }
}
