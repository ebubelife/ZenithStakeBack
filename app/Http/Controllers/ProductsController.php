<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use App\Models\Members;
use App\Models\Vendors;


class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //

        try{

           

            $validated = $request->validate([
                'productName' => 'required|string',
                'productDescription' => 'required|string',
                'vendor_id'=>  'required',
                'productPrice'=>  'required|string',
                'productCategory'=>  'required|string',
                'productCommission'=>  'required|string',
                'productSalesPageLink'=>  'required|string',
                'productTYLink'=>  'required|string',
                'productJVLink'=>  'required|string',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10280',
               
            ],
           
        );
    
       
     
        $product = new Products();
        $product ->productName = $validated['productName'];
        $product ->productDescription = $validated['productDescription'];
        $product ->vendor_id = $validated['vendor_id'];
        $product ->productPrice = $validated['productPrice'];
        $product ->productCategory = $validated['productCategory'];
        $product ->productCommission = $validated['productCommission'];
        $product ->productSalesPageLink = $validated['productSalesPageLink'];
        $product ->productTYLink = $validated['productTYLink'];
        $product ->productJVLink = $validated['productJVLink'];

        // Retrieve the uploaded file from the request
        $image = $request->file('image');

        // Generate a unique filename for the uploaded file
        $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();

        // Upload the file to the storage disk (public disk by default)
        $image->storeAs('public/images/product_images', $filename);

        // Return a success response with the URL of the uploaded file
       

                if( $image->storeAs('public/images/product_images', $filename)){
                    $product ->image = $filename;
                    $product ->save();
                    return response()->json(['message'=>'success'],200);
                }

                else{
                    return response()->json(['message'=>'Sorry, your image could not be uploaded!'],405);
                }
    
      
       
       
    
             
        }
        catch(\Exception $e){
            return response()->json(['message'=>'An error occured, please try again', 'error'=>$e],405);
    
    
        }
    }


    

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function show($count)
    {
      // Use the all() method to retrieve all products from the database

    if($count){
    $products = Products::take($count)->get();

    for($i=0; $i < count($products); $i++){
        $vendor_data1 = Members::find($products[$i]->vendor_id);
       // $vendor_data2 = Vendors::find($products[$i]->vendor_id);
        $vendor_data2 = Vendors::where('vendor_id', $products[$i]->vendor_id)->first();

        $products[$i]["vendor_data1"] = response()->json($vendor_data1);
        $products[$i]["vendor_data2"] = response()->json($vendor_data2);

        $products[$i]["image_path"] = asset('https://back.learniix.com/storage/images/product_images/' . $products[$i]["image"]);
 


        

    }

    // Return a JSON response containing the retrieved products
    return response()->json($products);

}

else{

    $products = Products::take($count)->get();

    for($i=0; $i < count($products); $i++){
        $vendor_data1 = Members::find($products[$i]->vendor_id);
       // $vendor_data2 = Vendors::find($products[$i]->vendor_id);
        $vendor_data2 = Vendors::where('vendor_id', $products[$i]->vendor_id)->first();

        $products[$i]["vendor_data1"] = response()->json($vendor_data1);
        $products[$i]["vendor_data2"] = response()->json($vendor_data2);

        $products[$i]["image_path"] = asset('https://back.learniix.com/storage/images/product_images/' . $products[$i]["image"]);
 


        

    }

    // Return a JSON response containing the retrieved products
    return response()->json($products);


}



    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function edit(Products $products)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Products $products)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Products  $products
     * @return \Illuminate\Http\Response
     */
    public function destroy(Products $products)
    {
        //
    }
}
