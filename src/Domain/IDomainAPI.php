<?php


namespace Habib\Hosting\Domain;


use Illuminate\Database\Eloquent\Model;

interface IDomainAPI
{
    /**
     * @param Model $owner
     * @return mixed
     */
    public function createPerson( $owner);

    /**
     * @param Model $DomainOrder
     * @param Model $owner
     * @return mixed
     */
    public function prolongDomain($DomainOrder, $owner);

    /**
     * @param string $domain
     * @return mixed
     */
    public function checkDomainAvailable( $domain);

    /**
     * @param array $domains
     * @return mixed
     */
    public function checkDomainsAvailable($domains);

    /**
     * @param Model $owner
     * @param $contract_id
     * @return mixed
     */
    public function createContactPerson(DomainOwner $owner, $contract_id);

    /**
     * @param Model $DomainOrder
     * @param Model $DomainOwner
     * @return mixed
     */
    public function registerDomain($DomainOrder,$DomainOwner);

    /**
     * @param Model $DomainOrder
     * @param $old_ns_array
     * @return mixed
     */
    public function changeNS($DomainOrder, $old_ns_array);

    /**
     * @param Model $domainOrder
     * @param Model $DomainOwner
     * @return mixed
     */
    public function changeContactPerson($domainOrder, $DomainOwner);

    /**
     * @return mixed
     */
    public function reqPool();

    /**
     * @return mixed
     */
    public function getErrorCode();


}
