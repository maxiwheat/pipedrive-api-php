<?php namespace Benhawker\Pipedrive\Library;

use Benhawker\Pipedrive\Exceptions\PipedriveMissingFieldError;

/**
 * Pipedrive Persons Methods
 *
 * Persons are your contacts, the customers you are doing Deals with.
 * Each Person can belong to an Organization.
 * Persons should not be confused with Users.
 *
 */
class Persons
{
    /**
     * Hold the pipedrive cURL session
     * @var \Benhawker\Pipedrive\Library\Curl Curl Object
     */
    protected $curl;

    /**
     * Initialise the object load master class
     */
    public function __construct(\Benhawker\Pipedrive\Pipedrive $master)
    {
        //associate curl class
        $this->curl = $master->curl();
    }

    /**
     * Returns a person
     *
     * @param  int   $id pipedrive persons id
     * @return array returns details of a person
     */
    public function getById($id)
    {
        return $this->curl->get('persons/' . $id);
    }

    /**
     * Returns a person / people
     *
     * @param  string $name pipedrive persons name
     * @return array  returns details of a person
     */
    public function getByName($name)
    {
        return $this->curl->get('persons/find', array('term' => $name));
    }
    
    /**
     * Returns all persons
     *
     * @param  array $data (filter_id, start, limit, sort_by, sort_mode)
     * @return array returns details of all products
     */
   public function getAll(array $data = array())
    {
        if (isset($data['pagination']) && $data['pagination'] == false) {
            unset($data['pagination']);
            return $this->getAllNoPagination($data);
        }
        
        return $this->curl->get('persons', $data);
    }
    /**
     * Returns all persons without pagination
     *
     * @param  array $data (filter_id, start, limit, sort_by, sort_mode)
     * @return array returns details of all products
     */
    private function getAllNoPagination(array $data = array())
    {
        $response = $this->curl->get('persons', array_merge($data, array('start' => 0, 'limit' => 500)));
        
        if ($response['success']) {
            $output = $response;
        
            $pagination = $response['additional_data']['pagination'];
            
            while ($pagination['more_items_in_collection']) {
                $response = $this->curl->get('persons', array_merge($data, array('start' => $pagination['next_start'], 'limit' => 500)));
                $pagination = $response['additional_data']['pagination'];
            
                $output['data'] = array_merge($output['data'], $response['data']);
            }
        }
        
        $output['additional_data']['pagination']['limit'] = count($output['data']);
        $output['additional_data']['pagination']['more_items_in_collection'] = false;
        
        if (isset($output['additional_data']['pagination']['next_start'])) {
            unset($output['additional_data']['pagination']['next_start']);
        }
        
        return $output;
    }

    /**
     * Lists deals associated with a person.
     *
     * @param  array $data (id, start, limit)
     * @return array deals
     */
    public function deals(array $data)
    {
        //if there is no name set throw error as it is a required field
        if (!isset($data['id'])) {
            throw new PipedriveMissingFieldError('You must include the "id" of the person when getting deals');
        }

        return $this->curl->get('persons/' . $data['id'] . '/deals');
    }

    /**
     * Updates a person
     *
     * @param  int   $personId pipedrives person Id
     * @param  array $data     new details of person
     * @return array returns details of a person
     */
    public function update($personId, array $data = array())
    {
        return $this->curl->put('persons/' . $personId, $data);
    }

    /**
     * Adds a person
     *
     * @param  array $data persons details
     * @return array returns details of a person
     */
    public function add(array $data)
    {
        //if there is no name set throw error as it is a required field
        if (!isset($data['name'])) {
            throw new PipedriveMissingFieldError('You must include a "name" field when inserting a person');
        }

        return $this->curl->post('persons', $data);
    }
}
