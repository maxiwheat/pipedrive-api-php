<?php namespace Benhawker\Pipedrive\Library;

use Benhawker\Pipedrive\Exceptions\PipedriveMissingFieldError;
use Benhawker\Pipedrive\Exceptions\PipedriveException;

/**
 * Pipedrive Organizations Methods
 *
 * Organizations are companies and other kinds of organizations you are making 
 * Deals with. Persons can be associated with organizations so that each 
 * organization can contain one or more Persons.
 *
 */
class Organizations
{
    /**
     * Hold the pipedrive cURL session
     * @var Curl Object
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
     * Returns a organization
     *
     * @param  int   $id pipedrive organizations id
     * @return array returns details of a organization
     */
    public function getById($id)
    {
        return $this->curl->get('organizations/' . $id);
    }

    /**
     * Returns an organization (or a list of organizations)
     *
     * @param  string $name pipedrive organizations name
     * @return array  returns details of a organization
     */
    public function getByName($name)
    {
        return $this->curl->get('organizations/find', array('term' => $name));
    }
    
    /**
     * Returns all organizations
     *
     * @param  array $data (filter_id, start, limit, sort_by, sort_mode)
     * @return array returns details of all organizations
     */
    public function getAll(array $data = array())
    {
        if (isset($data['pagination']) && $data['pagination'] == false) {
            unset($data['pagination']);
            return $this->getAllNoPagination($data);
        }
        
        return $this->curl->get('organizations', $data);
    }
    
    /**
     * Returns all organizations without pagination
     *
     * @param  array $data (filter_id, start, limit, sort_by, sort_mode)
     * @return array returns details of all organizations
     */
    private function getAllNoPagination(array $data = array())
    {
        $response = $this->curl->get('organizations', array_merge($data, array('start' => 0, 'limit' => 500)));
        
        if ($response['success']) {
            $output = $response;
        
            $pagination = $response['additional_data']['pagination'];
            
            while ($pagination['more_items_in_collection']) {
                $response = $this->curl->get('organizations', array_merge($data, array('start' => $pagination['next_start'], 'limit' => 500)));
                
                if (!$response['success']) {
                    throw new PipedriveException('One of the request did not succeed while retrieving all organizations');
                }
                
                $pagination = $response['additional_data']['pagination'];
            
                array_merge($output['data'], $response['data']);
            }
        } else {
            throw new PipedriveException('One of the request did not succeed while retrieving all organizations');
        }
        
        $output['additional_data']['pagination']['limit'] = count($output['data']);
        $output['additional_data']['pagination']['more_items_in_collection'] = false;
        
        if (isset($output['additional_data']['pagination']['next_start'])) {
            unset($output['additional_data']['pagination']['next_start']);
        }
        
        return $output;
    }

    /**
     * Lists deals associated with a organization.
     *
     * @param  array $data (id, start, limit)
     * @return array deals
     */
    public function deals(array $data)
    {
        //if there is no name set throw error as it is a required field
        if (!isset($data['id'])) {
            throw new PipedriveMissingFieldError('You must include the "id" of the organization when getting deals');
        }

        return $this->curl->get('organizations/' . $data['id'] . '/deals', $data);
    }
    
    /**
     * Lists persons associated with a organization.
     *
     * @param  array $data (id, start, limit)
     * @return array persons
     */
    public function persons(array $data)
    {
        //if there is no name set throw error as it is a required field
        if (!isset($data['id'])) {
            throw new PipedriveMissingFieldError('You must include the "id" of the organization when getting persons');
        }

        return $this->curl->get('organizations/' . $data['id'] . '/persons', $data);
    }

    /**
     * Updates an organization
     *
     * @param  int   $organizationId pipedrives organization Id
     * @param  array $data     new details of organization
     * @return array returns details of a organization
     */
    public function update($organizationId, array $data = array())
    {
        return $this->curl->put('organizations/' . $organizationId, $data);
    }

    /**
     * Adds a organization
     *
     * @param  array $data organizations details
     * @return array returns details of a organization
     */
    public function add(array $data)
    {
        //if there is no name set throw error as it is a required field
        if (!isset($data['name'])) {
            throw new PipedriveMissingFieldError('You must include a "name" field when inserting a organization');
        }

        return $this->curl->post('organizations', $data);
    }
    
    /**
     * Delete an organization
     *
     * @param  int   $id pipedrive organizations id
     */
    public function delete($id)
    {
        return $this->curl->delete('organizations/' . $id);
    }
}
