<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\TicketFilter;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\V1\TicketPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TicketController extends ApiController
{
    protected $policyClass = TicketPolicy::class;

    /**
     * GET All tickets
     * 
     * @group Managing Tickets
     * @queryParam sort string Data field(s) to sort by. Separate multiple fields with commas. Denote descending sort with a minus sign. Example: sort=title,-createdAt
     * @queryParam filter[status] Filter by status code: A, C, H, X. No-example
     * @queryParam filter[title] Filter by title. Wildcards is supported. Example: *title*
     */
    public function index(TicketFilter $filters)
    {
        return TicketResource::collection(Ticket::filter($filters)->paginate());

        // if ($this->include('author')) {
        //     return TicketResource::collection( Ticket::with('user')->paginate() );    
        // }

        // return TicketResource::collection( Ticket::paginate() );
    }

    /**
     * Create a ticket
     * 
     * Creates a new ticket. Users can create ticket for themeselves. Managers can create tickets for any user.
     * 
     * @group Managing Tickets
     * 
     */
    public function store(StoreTicketRequest $request)
    {
        if ($this->isAble('store', Ticket::class)) {
            return new TicketResource(Ticket::create($request->mappedAttributes()));
        }

        return $this->notAuthorized('You are not allowed to create that resource');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function show(Ticket $ticket)
    {
        if ($this->include('author')) {
            return new TicketResource($ticket->load('user'));
        }

        return new TicketResource($ticket);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTicketRequest  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        // PATCH
        if ($this->isAble('update', $ticket)) {
            $ticket->update( $request->mappedAttributes() );
    
            return new TicketResource( $ticket );
        }

        return $this->notAuthorized('You are not allowed to update that resource');
    }

    public function replace(ReplaceTicketRequest $request, Ticket $ticket)
    {
        //PUT
        if ($this->isAble('replace', $ticket)) {
            $ticket->update( $request->mappedAttributes() );
    
            return new TicketResource( $ticket );
        }

        return $this->notAuthorized('You are not allowed to replace that resource');
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ticket $ticket)
    {
        if ($this->isAble('delete', $ticket)) {
            $ticket->delete();
            return $this->ok('Ticket successfully deleted');
        }

        return $this->notAuthorized('You are not allowed to delete that resource');
    }
}
