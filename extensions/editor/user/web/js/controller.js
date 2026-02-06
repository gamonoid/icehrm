import ExtensionController from '../../../../../web/api/ExtensionController';

class EditorUserExtensionController extends ExtensionController {
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

  saveContent(hash, data) {
    // Making a simple POST request
    return this.getApiClient().post('editor/save-content', { hash, data });
  }

  updateQuizAnswers(hash, data) {
    // Making a simple POST request
    return this.getApiClient().post('editor/update-quiz-answers', { hash, data });
  }

}
export default EditorUserExtensionController;
