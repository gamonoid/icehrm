import ExtensionController from '../../../../../web/api/ExtensionController';

class KudosAdminExtensionController extends ExtensionController {
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
    this.getApiClient().get('kudos/echo').then((response) => {
      console.log('Response to kudos/echo GET request:');
      console.log(response);
    });

    // Making a simple POST request
    this.getApiClient().post('kudos/echo', { browserTime: (new Date()).getTime() }).then((response) => {
      console.log('Response to kudos/echo POST request:');
      console.log(response);
    });
  }
}
export default KudosAdminExtensionController;
