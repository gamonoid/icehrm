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
 * The "tasks" collection of methods.
 * Typical usage is:
 *  <code>
 *   $cloudtasksService = new Google_Service_CloudTasks(...);
 *   $tasks = $cloudtasksService->tasks;
 *  </code>
 */
class Google_Service_CloudTasks_Resource_ProjectsLocationsQueuesTasks extends Google_Service_Resource
{
  /**
   * Acknowledges a pull task.
   *
   * The worker, that is, the entity that leased this task must call this method
   * to indicate that the work associated with the task has finished.
   *
   * The worker must acknowledge a task within the lease_duration or the lease
   * will expire and the task will become available to be leased again. After the
   * task is acknowledged, it will not be returned by a later LeaseTasks, GetTask,
   * or ListTasks.
   *
   * To acknowledge multiple tasks at the same time, use [HTTP
   * batching](/storage/docs/json_api/v1/how-tos/batch) or the batching
   * documentation for your client library, for example
   * https://developers.google.com/api-client-library/python/guide/batch.
   * (tasks.acknowledge)
   *
   * @param string $name Required.
   *
   * The task name. For example:
   * `projects/PROJECT_ID/locations/LOCATION_ID/queues/QUEUE_ID/tasks/TASK_ID`
   * @param Google_Service_CloudTasks_AcknowledgeTaskRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudTasks_CloudtasksEmpty
   */
  public function acknowledge($name, Google_Service_CloudTasks_AcknowledgeTaskRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('acknowledge', array($params), "Google_Service_CloudTasks_CloudtasksEmpty");
  }
  /**
   * Cancel a pull task's lease.
   *
   * The worker can use this method to cancel a task's lease by setting its
   * schedule_time to now. This will make the task available to be leased to the
   * next caller of LeaseTasks. (tasks.cancelLease)
   *
   * @param string $name Required.
   *
   * The task name. For example:
   * `projects/PROJECT_ID/locations/LOCATION_ID/queues/QUEUE_ID/tasks/TASK_ID`
   * @param Google_Service_CloudTasks_CancelLeaseRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudTasks_Task
   */
  public function cancelLease($name, Google_Service_CloudTasks_CancelLeaseRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('cancelLease', array($params), "Google_Service_CloudTasks_Task");
  }
  /**
   * Creates a task and adds it to a queue.
   *
   * To add multiple tasks at the same time, use [HTTP
   * batching](/storage/docs/json_api/v1/how-tos/batch) or the batching
   * documentation for your client library, for example
   * https://developers.google.com/api-client-library/python/guide/batch.
   *
   * Tasks cannot be updated after creation; there is no UpdateTask command.
   *
   * * For [App Engine queues](google.cloud.tasks.v2beta2.AppEngineHttpTarget),
   * the maximum task size is 100KB. * For [pull
   * queues](google.cloud.tasks.v2beta2.PullTarget), this   the maximum task size
   * is 1MB. (tasks.create)
   *
   * @param string $parent Required.
   *
   * The queue name. For example:
   * `projects/PROJECT_ID/locations/LOCATION_ID/queues/QUEUE_ID`
   *
   * The queue must already exist.
   * @param Google_Service_CloudTasks_CreateTaskRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudTasks_Task
   */
  public function create($parent, Google_Service_CloudTasks_CreateTaskRequest $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_CloudTasks_Task");
  }
  /**
   * Deletes a task.
   *
   * A task can be deleted if it is scheduled or dispatched. A task cannot be
   * deleted if it has completed successfully or permanently failed.
   * (tasks.delete)
   *
   * @param string $name Required.
   *
   * The task name. For example:
   * `projects/PROJECT_ID/locations/LOCATION_ID/queues/QUEUE_ID/tasks/TASK_ID`
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudTasks_CloudtasksEmpty
   */
  public function delete($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_CloudTasks_CloudtasksEmpty");
  }
  /**
   * Gets a task. (tasks.get)
   *
   * @param string $name Required.
   *
   * The task name. For example:
   * `projects/PROJECT_ID/locations/LOCATION_ID/queues/QUEUE_ID/tasks/TASK_ID`
   * @param array $optParams Optional parameters.
   *
   * @opt_param string responseView The response_view specifies which subset of
   * the Task will be returned.
   *
   * By default response_view is BASIC; not all information is retrieved by
   * default because some data, such as payloads, might be desirable to return
   * only when needed because of its large size or because of the sensitivity of
   * data that it contains.
   *
   * Authorization for FULL requires `cloudtasks.tasks.fullView` [Google
   * IAM](/iam/) permission on the Task resource.
   * @return Google_Service_CloudTasks_Task
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_CloudTasks_Task");
  }
  /**
   * Leases tasks from a pull queue for lease_duration.
   *
   * This method is invoked by the worker to obtain a lease. The worker must
   * acknowledge the task via AcknowledgeTask after they have performed the work
   * associated with the task.
   *
   * The payload is intended to store data that the worker needs to perform the
   * work associated with the task. To return the payloads in the response, set
   * response_view to FULL.
   *
   * A maximum of 10 qps of LeaseTasks requests are allowed per queue.
   * RESOURCE_EXHAUSTED is returned when this limit is exceeded.
   * RESOURCE_EXHAUSTED is also returned when max_tasks_dispatched_per_second is
   * exceeded. (tasks.lease)
   *
   * @param string $parent Required.
   *
   * The queue name. For example:
   * `projects/PROJECT_ID/locations/LOCATION_ID/queues/QUEUE_ID`
   * @param Google_Service_CloudTasks_LeaseTasksRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudTasks_LeaseTasksResponse
   */
  public function lease($parent, Google_Service_CloudTasks_LeaseTasksRequest $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('lease', array($params), "Google_Service_CloudTasks_LeaseTasksResponse");
  }
  /**
   * Lists the tasks in a queue.
   *
   * By default, only the BASIC view is retrieved due to performance
   * considerations; response_view controls the subset of information which is
   * returned. (tasks.listProjectsLocationsQueuesTasks)
   *
   * @param string $parent Required.
   *
   * The queue name. For example:
   * `projects/PROJECT_ID/locations/LOCATION_ID/queues/QUEUE_ID`
   * @param array $optParams Optional parameters.
   *
   * @opt_param string responseView The response_view specifies which subset of
   * the Task will be returned.
   *
   * By default response_view is BASIC; not all information is retrieved by
   * default because some data, such as payloads, might be desirable to return
   * only when needed because of its large size or because of the sensitivity of
   * data that it contains.
   *
   * Authorization for FULL requires `cloudtasks.tasks.fullView` [Google
   * IAM](/iam/) permission on the Task resource.
   * @opt_param string orderBy Sort order used for the query. The only fields
   * supported for sorting are `schedule_time` and `pull_message.tag`. All results
   * will be returned in approximately ascending order. The default ordering is by
   * `schedule_time`.
   * @opt_param string pageToken A token identifying the page of results to
   * return.
   *
   * To request the first page results, page_token must be empty. To request the
   * next page of results, page_token must be the value of next_page_token
   * returned from the previous call to ListTasks method.
   *
   * The page token is valid for only 2 hours.
   * @opt_param int pageSize Requested page size. Fewer tasks than requested might
   * be returned.
   *
   * The maximum page size is 1000. If unspecified, the page size will be the
   * maximum. Fewer tasks than requested might be returned, even if more tasks
   * exist; use next_page_token in the response to determine if more tasks exist.
   * @return Google_Service_CloudTasks_ListTasksResponse
   */
  public function listProjectsLocationsQueuesTasks($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_CloudTasks_ListTasksResponse");
  }
  /**
   * Renew the current lease of a pull task.
   *
   * The worker can use this method to extend the lease by a new duration,
   * starting from now. The new task lease will be returned in the task's
   * schedule_time. (tasks.renewLease)
   *
   * @param string $name Required.
   *
   * The task name. For example:
   * `projects/PROJECT_ID/locations/LOCATION_ID/queues/QUEUE_ID/tasks/TASK_ID`
   * @param Google_Service_CloudTasks_RenewLeaseRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudTasks_Task
   */
  public function renewLease($name, Google_Service_CloudTasks_RenewLeaseRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('renewLease', array($params), "Google_Service_CloudTasks_Task");
  }
  /**
   * Forces a task to run now.
   *
   * When this method is called, Cloud Tasks will dispatch the task, even if the
   * task is already running, the queue has reached its RateLimits or is PAUSED.
   *
   * This command is meant to be used for manual debugging. For example, RunTask
   * can be used to retry a failed task after a fix has been made or to manually
   * force a task to be dispatched now.
   *
   * The dispatched task is returned. That is, the task that is returned contains
   * the status after the task is dispatched but before the task is received by
   * its target.
   *
   * If Cloud Tasks receives a successful response from the task's target, then
   * the task will be deleted; otherwise the task's schedule_time will be reset to
   * the time that RunTask was called plus the retry delay specified in the
   * queue's RetryConfig.
   *
   * RunTask returns NOT_FOUND when it is called on a task that has already
   * succeeded or permanently failed.
   *
   * RunTask cannot be called on a pull task. (tasks.run)
   *
   * @param string $name Required.
   *
   * The task name. For example:
   * `projects/PROJECT_ID/locations/LOCATION_ID/queues/QUEUE_ID/tasks/TASK_ID`
   * @param Google_Service_CloudTasks_RunTaskRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudTasks_Task
   */
  public function run($name, Google_Service_CloudTasks_RunTaskRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('run', array($params), "Google_Service_CloudTasks_Task");
  }
}
