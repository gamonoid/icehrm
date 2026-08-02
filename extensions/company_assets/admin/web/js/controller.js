import ExtensionController from '../../../../../web/api/ExtensionController';

class Company_assetsAdminExtensionController extends ExtensionController {
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
    this.getApiClient().get('company_assets/echo').then((response) => {
      console.log('Response to company_assets/echo GET request:');
      console.log(response);
    });

    // Making a simple POST request
    this.getApiClient().post('company_assets/echo', { browserTime: (new Date()).getTime() }).then((response) => {
      console.log('Response to company_assets/echo POST request:');
      console.log(response);
    });
  }
}
export default Company_assetsAdminExtensionController;
