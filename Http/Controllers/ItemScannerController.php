<?php

namespace Modules\ItemScanner\Http\Controllers;

use Auth;
use App\Http\Controllers\BaseController;
use App\Services\DatatableService;
use Modules\ItemScanner\Datatables\ItemScannerDatatable;
use Modules\ItemScanner\Repositories\ItemScannerRepository;
use Modules\ItemScanner\Http\Requests\ItemScannerRequest;
use Modules\ItemScanner\Http\Requests\CreateItemScannerRequest;
use Modules\ItemScanner\Http\Requests\UpdateItemScannerRequest;

class ItemScannerController extends BaseController
{
    protected $ItemScannerRepo;
    //protected $entityType = 'itemscanner';

    public function __construct(ItemScannerRepository $itemscannerRepo)
    {
        //parent::__construct();

        $this->itemscannerRepo = $itemscannerRepo;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('list_wrapper', [
            'entityType' => 'itemscanner',
            'datatable' => new ItemScannerDatatable(),
            'title' => mtrans('itemscanner', 'itemscanner_list'),
        ]);
    }

    public function datatable(DatatableService $datatableService)
    {
        $search = request()->input('sSearch');
        $userId = Auth::user()->filterId();

        $datatable = new ItemScannerDatatable();
        $query = $this->itemscannerRepo->find($search, $userId);

        return $datatableService->createDatatable($datatable, $query);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(ItemScannerRequest $request)
    {
        $data = [
            'itemscanner' => null,
            'method' => 'POST',
            'url' => 'itemscanner',
            'title' => mtrans('itemscanner', 'new_itemscanner'),
        ];

        return view('itemscanner::edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(CreateItemScannerRequest $request)
    {
        $itemscanner = $this->itemscannerRepo->save($request->input());

        return redirect()->to($itemscanner->present()->editUrl)
            ->with('message', mtrans('itemscanner', 'created_itemscanner'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(ItemScannerRequest $request)
    {
        $itemscanner = $request->entity();

        $data = [
            'itemscanner' => $itemscanner,
            'method' => 'PUT',
            'url' => 'itemscanner/' . $itemscanner->public_id,
            'title' => mtrans('itemscanner', 'edit_itemscanner'),
        ];

        return view('itemscanner::edit', $data);
    }

    /**
     * Show the form for editing a resource.
     * @return Response
     */
    public function show(ItemScannerRequest $request)
    {
        return redirect()->to("itemscanner/{$request->itemscanner}/edit");
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(UpdateItemScannerRequest $request)
    {
        $itemscanner = $this->itemscannerRepo->save($request->input(), $request->entity());

        return redirect()->to($itemscanner->present()->editUrl)
            ->with('message', mtrans('itemscanner', 'updated_itemscanner'));
    }

    /**
     * Update multiple resources
     */
    public function bulk()
    {
        $action = request()->input('action');
        $ids = request()->input('public_id') ?: request()->input('ids');
        $count = $this->itemscannerRepo->bulk($ids, $action);

        return redirect()->to('itemscanner')
            ->with('message', mtrans('itemscanner', $action . '_itemscanner_complete'));
    }
}
