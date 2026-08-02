import ExtensionController from '../../../../../web/api/ExtensionController';

class DirectoryUserExtensionController extends ExtensionController {
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
    this.getApiClient().get('directory/echo').then((response) => {
      console.log('Response to directory/echo GET request:');
      console.log(response);
    });

    // Making a simple POST request
    this.getApiClient().post('directory/echo', { browserTime: (new Date()).getTime() }).then((response) => {
      console.log('Response to directory/echo POST request:');
      console.log(response);
    });
  }
}
export default DirectoryUserExtensionController;
