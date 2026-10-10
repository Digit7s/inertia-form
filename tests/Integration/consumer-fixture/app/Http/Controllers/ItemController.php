<?php

namespace App\Http\Controllers;

use App\Forms\ItemForm;
use App\Http\Requests\StoreItemRequest;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Items/Form', [
            'formPayload' => ItemForm::make()->action(route('items.store'))->toArray(),
            'recordId' => null,
        ]);
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['attachment'] = $request->file('attachment')?->store('items', 'public');
        $item = Item::create($data);

        return redirect()->route('items.edit', $item)->with('success', 'Item created.');
    }

    public function edit(Item $item): Response
    {
        return Inertia::render('Items/Form', [
            'formPayload' => ItemForm::make($item)->action(route('items.update', $item))->toArray(),
            'recordId' => $item->id,
        ]);
    }

    public function update(StoreItemRequest $request, Item $item): RedirectResponse
    {
        $data = $request->validated();
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('items', 'public');
        } else {
            // This form has no explicit remove-file control, so null/empty input
            // must not silently discard the saved upload during an unrelated edit.
            unset($data['attachment']);
        }
        unset($data['password']);
        $item->update($data);

        return redirect()->route('items.edit', $item)->with('success', 'Item updated.');
    }
}
