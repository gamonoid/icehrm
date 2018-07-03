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

class Google_Service_Analytics_AccountTreeRequestAccountSettings extends Google_Model
{
  public $shareAnonymouslyWithOthers;
  public $shareWithGoogleProducts;
  public $shareWithSpecialists;
  public $shareWithSupport;

  public function setShareAnonymouslyWithOthers($shareAnonymouslyWithOthers)
  {
    $this->shareAnonymouslyWithOthers = $shareAnonymouslyWithOthers;
  }
  public function getShareAnonymouslyWithOthers()
  {
    return $this->shareAnonymouslyWithOthers;
  }
  public function setShareWithGoogleProducts($shareWithGoogleProducts)
  {
    $this->shareWithGoogleProducts = $shareWithGoogleProducts;
  }
  public function getShareWithGoogleProducts()
  {
    return $this->shareWithGoogleProducts;
  }
  public function setShareWithSpecialists($shareWithSpecialists)
  {
    $this->shareWithSpecialists = $shareWithSpecialists;
  }
  public function getShareWithSpecialists()
  {
    return $this->shareWithSpecialists;
  }
  public function setShareWithSupport($shareWithSupport)
  {
    $this->shareWithSupport = $shareWithSupport;
  }
  public function getShareWithSupport()
  {
    return $this->shareWithSupport;
  }
}
