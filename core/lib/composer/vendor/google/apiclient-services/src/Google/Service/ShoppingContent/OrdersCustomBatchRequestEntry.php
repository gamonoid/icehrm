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

class Google_Service_ShoppingContent_OrdersCustomBatchRequestEntry extends Google_Model
{
  public $batchId;
  protected $cancelType = 'Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryCancel';
  protected $cancelDataType = '';
  protected $cancelLineItemType = 'Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryCancelLineItem';
  protected $cancelLineItemDataType = '';
  protected $inStoreRefundLineItemType = 'Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryInStoreRefundLineItem';
  protected $inStoreRefundLineItemDataType = '';
  public $merchantId;
  public $merchantOrderId;
  public $method;
  public $operationId;
  public $orderId;
  protected $refundType = 'Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryRefund';
  protected $refundDataType = '';
  protected $rejectReturnLineItemType = 'Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryRejectReturnLineItem';
  protected $rejectReturnLineItemDataType = '';
  protected $returnLineItemType = 'Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryReturnLineItem';
  protected $returnLineItemDataType = '';
  protected $returnRefundLineItemType = 'Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryReturnRefundLineItem';
  protected $returnRefundLineItemDataType = '';
  protected $setLineItemMetadataType = 'Google_Service_ShoppingContent_OrdersCustomBatchRequestEntrySetLineItemMetadata';
  protected $setLineItemMetadataDataType = '';
  protected $shipLineItemsType = 'Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryShipLineItems';
  protected $shipLineItemsDataType = '';
  protected $updateLineItemShippingDetailsType = 'Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryUpdateLineItemShippingDetails';
  protected $updateLineItemShippingDetailsDataType = '';
  protected $updateShipmentType = 'Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryUpdateShipment';
  protected $updateShipmentDataType = '';

  public function setBatchId($batchId)
  {
    $this->batchId = $batchId;
  }
  public function getBatchId()
  {
    return $this->batchId;
  }
  /**
   * @param Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryCancel
   */
  public function setCancel(Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryCancel $cancel)
  {
    $this->cancel = $cancel;
  }
  /**
   * @return Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryCancel
   */
  public function getCancel()
  {
    return $this->cancel;
  }
  /**
   * @param Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryCancelLineItem
   */
  public function setCancelLineItem(Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryCancelLineItem $cancelLineItem)
  {
    $this->cancelLineItem = $cancelLineItem;
  }
  /**
   * @return Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryCancelLineItem
   */
  public function getCancelLineItem()
  {
    return $this->cancelLineItem;
  }
  /**
   * @param Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryInStoreRefundLineItem
   */
  public function setInStoreRefundLineItem(Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryInStoreRefundLineItem $inStoreRefundLineItem)
  {
    $this->inStoreRefundLineItem = $inStoreRefundLineItem;
  }
  /**
   * @return Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryInStoreRefundLineItem
   */
  public function getInStoreRefundLineItem()
  {
    return $this->inStoreRefundLineItem;
  }
  public function setMerchantId($merchantId)
  {
    $this->merchantId = $merchantId;
  }
  public function getMerchantId()
  {
    return $this->merchantId;
  }
  public function setMerchantOrderId($merchantOrderId)
  {
    $this->merchantOrderId = $merchantOrderId;
  }
  public function getMerchantOrderId()
  {
    return $this->merchantOrderId;
  }
  public function setMethod($method)
  {
    $this->method = $method;
  }
  public function getMethod()
  {
    return $this->method;
  }
  public function setOperationId($operationId)
  {
    $this->operationId = $operationId;
  }
  public function getOperationId()
  {
    return $this->operationId;
  }
  public function setOrderId($orderId)
  {
    $this->orderId = $orderId;
  }
  public function getOrderId()
  {
    return $this->orderId;
  }
  /**
   * @param Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryRefund
   */
  public function setRefund(Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryRefund $refund)
  {
    $this->refund = $refund;
  }
  /**
   * @return Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryRefund
   */
  public function getRefund()
  {
    return $this->refund;
  }
  /**
   * @param Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryRejectReturnLineItem
   */
  public function setRejectReturnLineItem(Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryRejectReturnLineItem $rejectReturnLineItem)
  {
    $this->rejectReturnLineItem = $rejectReturnLineItem;
  }
  /**
   * @return Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryRejectReturnLineItem
   */
  public function getRejectReturnLineItem()
  {
    return $this->rejectReturnLineItem;
  }
  /**
   * @param Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryReturnLineItem
   */
  public function setReturnLineItem(Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryReturnLineItem $returnLineItem)
  {
    $this->returnLineItem = $returnLineItem;
  }
  /**
   * @return Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryReturnLineItem
   */
  public function getReturnLineItem()
  {
    return $this->returnLineItem;
  }
  /**
   * @param Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryReturnRefundLineItem
   */
  public function setReturnRefundLineItem(Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryReturnRefundLineItem $returnRefundLineItem)
  {
    $this->returnRefundLineItem = $returnRefundLineItem;
  }
  /**
   * @return Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryReturnRefundLineItem
   */
  public function getReturnRefundLineItem()
  {
    return $this->returnRefundLineItem;
  }
  /**
   * @param Google_Service_ShoppingContent_OrdersCustomBatchRequestEntrySetLineItemMetadata
   */
  public function setSetLineItemMetadata(Google_Service_ShoppingContent_OrdersCustomBatchRequestEntrySetLineItemMetadata $setLineItemMetadata)
  {
    $this->setLineItemMetadata = $setLineItemMetadata;
  }
  /**
   * @return Google_Service_ShoppingContent_OrdersCustomBatchRequestEntrySetLineItemMetadata
   */
  public function getSetLineItemMetadata()
  {
    return $this->setLineItemMetadata;
  }
  /**
   * @param Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryShipLineItems
   */
  public function setShipLineItems(Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryShipLineItems $shipLineItems)
  {
    $this->shipLineItems = $shipLineItems;
  }
  /**
   * @return Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryShipLineItems
   */
  public function getShipLineItems()
  {
    return $this->shipLineItems;
  }
  /**
   * @param Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryUpdateLineItemShippingDetails
   */
  public function setUpdateLineItemShippingDetails(Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryUpdateLineItemShippingDetails $updateLineItemShippingDetails)
  {
    $this->updateLineItemShippingDetails = $updateLineItemShippingDetails;
  }
  /**
   * @return Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryUpdateLineItemShippingDetails
   */
  public function getUpdateLineItemShippingDetails()
  {
    return $this->updateLineItemShippingDetails;
  }
  /**
   * @param Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryUpdateShipment
   */
  public function setUpdateShipment(Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryUpdateShipment $updateShipment)
  {
    $this->updateShipment = $updateShipment;
  }
  /**
   * @return Google_Service_ShoppingContent_OrdersCustomBatchRequestEntryUpdateShipment
   */
  public function getUpdateShipment()
  {
    return $this->updateShipment;
  }
}
