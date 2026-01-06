namespace App\Controller;

use App\Controller\AppController;

class CustomerssController extends AppController
{
    public function testCharset()
    {
        $connection = \Cake\Datasource\ConnectionManager::get('default');
        $results = $connection->execute('SHOW VARIABLES LIKE "character_set%"')->fetchAll('assoc');

        debug($results); // CakePHP will display nicely
        die; // stop further execution for testing
    }
}
