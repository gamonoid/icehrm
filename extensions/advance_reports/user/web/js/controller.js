import ExtensionController from '../../../../../web/api/ExtensionController';

class Advance_reportsUserExtensionController extends ExtensionController {
  handleTestAction() {
    /**
     * The `testAction` should be defined in the `Controller.php` class
     */
    this.handleRequest(
      'testAction',
      { data: 'message from client' },
    ).then((response) => {
      console.log(response);
    });
  }

  makeSomeTestApiRequests() {
    /**
     * The endpoints are defined in ApiController.php
     */

    // Making a simple GET request
    this.getApiClient().get('advance_reports/echo').then((response) => {
      console.log('Response to advance_reports/echo GET request:');
      console.log(response);
    });

    // Making a simple POST request
    this.getApiClient().post('advance_reports/echo', { browserTime: (new Date()).getTime() }).then((response) => {
      console.log('Response to advance_reports/echo POST request:');
      console.log(response);
    });
  }
}
export default Advance_reportsUserExtensionController;
