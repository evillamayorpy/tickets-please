<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\TicketFilter;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Models\Ticket;
use App\Policies\V1\TicketPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PharIo\Manifest\Author;

class AuthorTicketsControllers extends ApiController
{
    protected $policyClass = TicketPolicy::class;

    public function index($author_id, TicketFilter $filters)
    {
        return TicketResource::collection(
            Ticket::where('user_id', $author_id)->filter($filters)->paginate()
        );
    }

    public function store(StoreTicketRequest $request)
    {
        if ($this->isAble('store', Ticket::class)) {
            return new TicketResource(Ticket::create($request->mappedAttributes([
                'author' => 'user_id'
            ])));
        }

        return $this->error('You are not allowed to create that resource', 401);
    }

    public function replace(ReplaceTicketRequest $request, Author $author, Ticket $ticket)
    {
        // PUT

        if ($this->isAble('replace', $ticket)) {
            $ticket->update($request->mappedAttributes([
                'author' => 'user_id'
            ]));
    
            return new TicketResource( $ticket );
        }

        return $this->notAuthorized('You are not allowed to update that resource');
        
    }

    public function update(UpdateTicketRequest $request, Author $author, Ticket $ticket)
    {
        // PATCH
        if ($this->isAble('update', $ticket)) {
            $ticket->update( $request->mappedAttributes() );
            return new TicketResource( $ticket );
        }

        return $this->notAuthorized('You are not allowed to update that resource');
        
    }

    public function destroy(Author $author, Ticket $ticket)
    {
        if ($this->isAble('delete', $ticket)) {
            $ticket->delete();
            return $this->ok('Ticket successfully deleted');
        }


        return $this->notAuthorized('You are not allowed to delete that resource', 401);
    }
}
