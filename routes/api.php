<?php

use Illuminate\Http\Request;
use App\Http\Controllers\MembersController;
use App\Models\Members;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VendorsController;

use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\BanksController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\NotificationsController;

use App\Models\Vendors;
use App\Models\Products;
use App\Models\Notification;
use App\Models\Transactions;
use App\Models\Sales;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Mail\MessageEmail;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(TransactionsController::class)->group(function(){
    Route::post('transaction/vendor/reg', 'store');
    Route::get('transactions/view/all', 'show');
   
   // Route::get('transactions/view/affiliate_payments', 'store_product');

    //retrieve affiliate payments

    Route::get('transactions/view/affiliate_payments/{id}', function ($id) {
        $affiliate_payments = Transactions::where('tx_type', 'AFFILIATE_PAYMENT')
        ->where('user_id',$id)
        ->orderByDesc('created_at')
       
        ->get();
    
        return response()->json( $affiliate_payments);
    });

    Route::get('transactions/view/affiliate_payments/all', function () {
        $affiliate_payments = Transactions::where('tx_type', 'AFFILIATE_PAYMENT')
       // ->where('user_id',$id)
        ->orderByDesc('created_at')
       
        ->get();
    
        return response()->json( $ffiliate_payments);
    });

    Route::get('transactions/view/vendor_payments/{id}', function ($id) {
        $vendor_payments = Transactions::where('tx_type', 'VENDOR_PAYMENT')
        ->where('user_id',$id)
        ->orderByDesc('created_at')
        ->get();
    
        return response()->json($vendor_payments);
    });


 

    Route::get('pay/affiliates', 'pay_affiliates');

    Route::get('pay/vendors', 'pay_vendors');

    

});





Route::controller(MembersController::class)->group(function(){
    Route::post('signup', 'store');
    Route::post('login', 'login');
    Route::post('verify_email_otp', 'verify_email_otp');
    Route::post('send_email', 'send_email');
    Route::get('test','test');
    Route::post('addVendor','createVendor');
    Route::post('update/isvendor','update');
    Route::get('email/test','test_email');
    Route::post('account/recover','send_mail_code');

    Route::get('/emails/send_aff_emails','send_email_to_all_affiliates');
    //Route::get('view/user/{id}', 'view_user');






       /*code to update members database */


     
      
       Route::get('update/aff/data/', function () {

        $json_d = '[
            
            ]';


            $json = json_decode($json_d, true);

            if (!is_array($json)) {
                return response()->json(['message' => 'Invalid JSON data'], 400);
            }

            $count = count($json);
    
            foreach ($json as $data) {
                $id = $data['id'];
                $totalAffSalesCash = $data['total_aff_sales_cash'];

                $user = Members::find($data['id']);

                $user->total_aff_sales_cash = $totalAffSalesCash;

                $user->save();
    
                // Update the MySQL database column using Eloquent
               // Members::where('id', $id)->update(['total_aff_sales_cash' => $totalAffSalesCash]);
            }
    
            return response()->json(['message' => ' updated successfully   '.strval($count)], 200);
       





       });









    //temp code
    Route::get('test/koretsales', function () {
        $phone = "2348037482777";
        $pin = "0115";
        $activation_code = "123456";
        $rand = "83637728787";
        $url = "https://www.koretsales.com/api/signin";
        
        $response = Http::asForm()->post($url, [
            'phone' => $phone,
            'pin' => $pin,
            'activation_code' => $activation_code,
            'rand' => $rand
        ]);
        
        $statusCode = $response->status();
        $responseData = $response->json();
        
        // Handle the response as needed
        if ($statusCode === 200) {

            // Generate a unique file name
$fileName = 'data_' . Str::random(10) . '.csv';

// Create a new file in the storage directory
$filePath = storage_path('app/' . $fileName);

// Open the file in write mode
$file = fopen($filePath, 'w');


// Write the CSV header
$header = ['stock_id', 'name',"price","quantity","date_added"  ];
fputcsv($file, $header);

// Fetch data from the database or any other source

$data = array();

 // Successful response
           // return $responseData["data"]["stocks"];

foreach($responseData["data"]["stocks"] as $single_stock){

    array_push($data, array($single_stock["stock_id"], $single_stock["name"], $single_stock["price"], $single_stock["quantity"], $single_stock["date_added"]));

}


// Write the data rows to the CSV file
foreach ($data as $row) {
    fputcsv($file, $row);
}

// Close the file
fclose($file);

// Store the CSV file in a public directory (optional)
$publicPath = 'public/csv/' . $fileName;
Storage::disk('local')->put($publicPath, file_get_contents($filePath));

// Optionally, you can delete the temporary file
unlink($filePath);

// Return a response with the download link
$downloadLink = Storage::url($publicPath);





return response()->json(['download_link' => $downloadLink]);
           
        } else {
            // Error handling
            return response()->json(['error' => 'An error occurred.'], $statusCode);
        }
    });

    

    //get affiliates with pending payment

    Route::get('view/payable_affiliates', function () {

    $unpaid_affiliates = Members::where("is_vendor", false)
    ->where("payment_reference_paystack","!=",null)
    ->whereRaw("CAST(unpaid_balance AS UNSIGNED) > 200 ")
    ->where("withdrawal_settings", true)
   /* ->whereIn("email", [
        "nonsojoshua001@gmail.com",
        "aimchinaza3039@gmail.com",
        "johnadetunji92@gmail.com",
        "belovedprinz@gmail.com",
        "blessingochiemen01@gmail.com"
    ])*/
    ->get();

    // Generate a unique file name
$fileName = 'data_' . Str::random(10) . '.csv';

// Create a new file in the storage directory
$filePath = storage_path('app/' . $fileName);

// Open the file in write mode
$file = fopen($filePath, 'w');

// Write the CSV header
$header = ['Account Number', 'Bank', 'Amount', 'Narration'];
fputcsv($file, $header);

// Fetch data from the database or any other source

$data = array();

foreach($unpaid_affiliates as $unpaid_affiliate){

    array_push($data, array($unpaid_affiliate->bank_account_number, $unpaid_affiliate->bank, $unpaid_affiliate->unpaid_balance,"ZENITHSTAKE ENTERPRISE - ".$unpaid_affiliate->firstName));

}


// Write the data rows to the CSV file
foreach ($data as $row) {
    fputcsv($file, $row);
}

// Close the file
fclose($file);

// Store the CSV file in a public directory (optional)
$publicPath = 'public/csv/' . $fileName;
Storage::disk('local')->put($publicPath, file_get_contents($filePath));

// Optionally, you can delete the temporary file
unlink($filePath);

// Return a response with the download link
$downloadLink = Storage::url($publicPath);





return response()->json(['download_link' => $downloadLink,"unpaid_affiliates" => $unpaid_affiliates]);


   // return response()->json($unpaid_affiliates );

    });


    Route::get('view/all', 'show');




  //retrieve affiliates , order by total unpaid balance

Route::get('view/affiliates/order', function () {
    $affiliates = Members::orderBy('unpaid_balance', 'desc')->get();
    
    return response()->json($affiliates);
});







    Route::get('view/affiliates', function () {
        $affiliates = Members::where('is_vendor', false)
        ->orderByDesc('created_at')
       // ->limit(100)
        ->get();
    
        return response()->json( $affiliates);
    });

    Route::get('view/affiliates/get/{records_limit}', function ($records_limit) {
        // Limit the query to the first 500 records
        $affiliates = Members::where('is_vendor', false)
            ->orderByDesc('created_at')
            ->take(min($records_limit, 500))
            ->get();
    
        return response()->json($affiliates);
    });

    Route::get('view/affiliates/search/{search_term}', function ($search_term) {
        // Limit the query to the first 500 records
        $affiliates = Members::where('is_vendor', false)
            ->where('email', 'like', '%' . $search_term . '%')
            ->orderByDesc('created_at')
            ->get();
    
        return response()->json($affiliates);
    });

    Route::get('view/affiliates/unpaid/total', function () {

       
      
        $affiliates = Members::where('is_vendor', false)
          ->where('unpaid_balance',"!=","0.00")
           // ->orderByDesc('created_at')
           ->whereBetween('last_sale_time', ["2023-12-10", "2023-12-12"])
            ->where("id","!=",507)
            ->orderByDesc('unpaid_balance')
            ->get();
    
       
    
        return response()->json([
         
            'sum_total_unpaid'=> $affiliates,
        ]);
    });

    Route::get('view/vendors', function () {
        $vendors = Members::where('is_vendor', true)
        ->orderByDesc('created_at')
        ->get();
    
        return response()->json( $vendors);
    });




    Route::get('view/user/{id}', function ($id) {
        $user = Members::find($id);
    
        return response()->json($user);
    });

    Route::get('user/update_vendor/{id}', function ($id) {
        $vendor = Members::findOrFail($id); // Find the vendor by ID
        $vendor->is_vendor = false; // Set the status to true
        $vendor->save(); // Save the changes to the database
        return response()->json(["message" => "successfully updated"]);
    });


    Route::get('users/all/update_all_earnings', function () {
       // Members::query()->update(['is_payed' => ]);



       Members::query()->update(['unpaid_balance' => "0.00"]);
       Members::query()->update(['unpaid_balance_vendor' => "0.00"]);
       Members::query()->update(['total_aff_sales_cash' => "0.00"]);
       Members::query()->update(['total_aff_sales' => "0.00"]);
       Members::query()->update(['total_vendor_sales_cash' => "0.00"]);
       Members::query()->update(['total_vendor_sales' => "0.00"]);

       return response()->json(["message" => "successfully updated"]);
    });

    Route::get('account/remove/{id}', function ($id) {
        $user = Members::destroy($id);

        return response()->json(["message" => "successfully deleted"]);
    });

    Route::post('member/update','update');

    Route::get('member/update_withdrawal/{id}', function ($id) {
        $user = Members::findOrFail($id);
    
    // Toggle the withdrawal_setting value
    $user->withdrawal_settings = !$user->withdrawal_settings;
    $user->save();

    return response()->json(["message" => "Withdrawal setting updated successfully"]);

    });

   

    Route::post('member/update_profile_admin','update_profile_admin_affiliate');

      Route::post('member/update_profile_admin_vendor','update_profile_admin_vendor');

    Route::post('account/verify_code','verify_code');
    Route::post('account/change_password','change_password');

    Route::post('account/confirm_email','send_mail_verify_code');

    Route::post('account/verify_email','verify_email_with_code');


    //verify email from admin
    Route::get('update_email_verified/{id}', function ($id) {
        // Find the user by ID
        $user = Members::find($id);
    
        if ($user) {
            // Update the email_verified column to true
          //  $user->update(['email_verified' => true]);

            $user->email_verified = true;
            $user->save();
    
            return response()->json(['message' => 'Email verification status updated successfully']);
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    });

   

    
   
  
   // Route::middleware('auth:sanctum')-> post('set_transaction_pin','set_transaction_pin');
   
   // Route::get('test_api','test_api');
});
//Vendors queries

Route::controller(VendorsController::class)->group(function(){

    Route::post('vendors/create','store');
   
   // Route::middleware('auth:sanctum')-> post('set_transaction_pin','set_transaction_pin');
   
   // Route::get('test_api','test_api');

   Route::get('view/vendor/{id}', function ($id) {
    $user = Vendors::where('vendor_id', $id)->get();

    return response()->json($user);
});




Route::get('vendors/view', function () {
    $vendors = Vendors::all();



    for($i=0; $i < count($vendors); $i++){

        $user = Members::find($vendors[$i]['vendor_id']);

        $vendors[$i]['vendor_details'] = $user;
        $vendors[$i]["image_path"] = asset('https://back.zenithstake.com/storage/images/vendor_images/' . $vendors[$i]["image"]);

    }

    return response()->json($vendors);
});




Route::get('vendors/top/view', function () {
    $vendors = Vendors::all();

    foreach ($vendors as $vendor) {
        $salesCount = Sales::where('vendor_id', $vendor->vendor_id)->count();
        $vendor->sales_count = $salesCount;

        $user = Members::where('is_payed', "true")
                         ->where("id",$vendor->vendor_id)->get();
        $vendor["vendor_details"] = $user;
        $vendor->image_path = asset('https://back.zenithstake.com/storage/images/vendor_images/' . $vendor->image);
    }

    // Sort vendors based on sales_count in descending order
    $vendors = $vendors->sortByDesc('sales_count')->values();

    // Limit the number of results to two
    $limitedVendors = $vendors->take(2);

    return response()->json($limitedVendors);
});




});

Route::controller(ProductsController::class)->group(function(){
  
    Route::post('products/add','store');
    Route::get('products/view/{count}','show');
    Route::get('approved_products/view/{count}','show_approved');
   // Route::middleware('auth:sanctum')-> post('set_transaction_pin','set_transaction_pin');
   
   // Route::get('test_api','test_api');

   Route::get('update/product/{id}', function ($id) {
    // Get the product by ID
    $product = Products::find($id);

    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }

    // Update the sales_page column
    $product->productSalesPageLink = 'https://zenithstake.com/smac/smac';
   // $product->vendor_id = "54";
    //$product->productCommission ="50";

    $product->save();

    return response()->json(['message' => 'Product sales page updated successfully']);
});

Route::get('approve/product/{id}', function ($id) {
    // Get the product by ID
    $product = Products::find($id);

    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }

    // Update the sales_page column
    $product->approved = true;
    $product->approved_date = Carbon::now();
   
    $product->save();

    return response()->json(['message' => 'Product sales page updated successfully']);
});

Route::get('disable/product/{id}', function ($id) {
    // Get the product by ID
    $product = Products::find($id);

    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }

    // Update the sales_page column
    $product->approved = false;
    $product->approved_date = null;
   
    $product->save();

    return response()->json(['message' => 'Product sales page updated successfully']);
});

//get products from vendors

Route::get('view/vendor_products/{vendor_id}', function ($vendor_id) {

    $vendor_products = Products::where('vendor_id', $vendor_id)->get();

    return response()->json($vendor_products);


});

Route::get('view/product/{id}', function ($id) {

    $product = Products::find($id);

    return response()->json($product);


});




});


Route::controller(BanksController::class)->group(function(){
  
    Route::post('banks/add_nigerian_banks','addNigerianBanks');

    Route::post('banks/add_ghana_banks','addGhanaBanks');

    
    Route::get('banks/view','show');

    Route::get('banks/view/gh','show_ghana');

    Route::get('banks/view/ng','show_nigeria');
  
});

Route::controller(SalesController::class)->group(function(){

    Route::get('sales/consolidate','consolidateSales');
  
    Route::post('sales/test_email','testemail');
    Route::post('sales/add','store');
    Route::get('sales/view','show');
    Route::get('view/sale/{id}', function ($id) {
        $sale = Sales::find($id);
    
        return response()->json($sale);
    });


    //get affiliate_sales in last 24 hours

    Route::get('sales/today/affiliate/{affiliate_id}', function ($affiliate_id) {
        $startDateTime = Carbon::today(); // Get the start of today (12am)
        $endDateTime = Carbon::now(); // Get the current date and time
    
        $sales = Sales::where('affiliate_id', $affiliate_id)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->get();
    
        return response()->json($sales);
    });


    //get sales between dates



    Route::get('sales/get_sales_dates', function (Request $request) {
        // Get the start date and end date from query parameters
        $startDate = '2023-09-08';

        $endDate = '2023-09-19';
    
        // Validate if start_date and end_date are provided and in valid date format
        if (!$startDate || !$endDate || !Carbon::parse($startDate) || !Carbon::parse($endDate)) {
            return response()->json(['error' => 'Invalid start_date or end_date format'], 400);
        }
    
        // Parse the dates to Carbon instances
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();
    
        // Query the sales records between the provided dates
        $sales = Sales::whereBetween('created_at', [$startDateTime, $endDateTime])->get();


                // Generate a unique file name
        $fileName = 'data_' . Str::random(10) . '.csv';

        // Create a new file in the storage directory
        $filePath = storage_path('app/' . $fileName);

        // Open the file in write mode
        $file = fopen($filePath, 'w');


        // Write the CSV header
        $header = ['sale_id', 'c_name',"c_email","c_phone","date_added"  ];
        fputcsv($file, $header);

        // Fetch data from the database or any other source

        $data = array();

        // Successful response
                // return $responseData["data"]["stocks"];

        foreach($sales as $sale){

            array_push($data, array($sale["id"], $sale["customer_name"], $sale["customer_email"], $sale["customer_phone"], $sale["created_at"]));

            try {
                if(Mail::to($sale["customer_email"])->send(new MessageEmail($sale["customer_email"]))){

                    /// return true;
         
                 }
            } catch (\Swift_TransportException $e) {
                // Log the error for debugging
                Log::error('Mailgun Error: ' . $e->getMessage());

                print('Mailgun Error: ' . $e->getMessage());
                // Handle the error gracefully, e.g., notify the user
            }
            
            
            
            
           

        }


        // Write the data rows to the CSV file
        foreach ($data as $row) {
            fputcsv($file, $row);
        }

        // Close the file
        fclose($file);

        // Store the CSV file in a public directory (optional)
        $publicPath = 'public/csv/' . $fileName;
        Storage::disk('local')->put($publicPath, file_get_contents($filePath));

        // Optionally, you can delete the temporary file
        unlink($filePath);

        // Return a response with the download link
        $downloadLink = Storage::url($publicPath);

    
        return response()->json(array("csv"=>$publicPath,"sales"=>$downloadLink));
    });
    

    //get vendor sales in last 24 hours

    Route::get('sales/today/vendor/{vendor_id}', function ($vendor_id) {
        $startDateTime = Carbon::today(); // Get the start of today (12am)
        $endDateTime = Carbon::now(); // Get the current date and time
    
        $sales = Sales::where('vendor_id', $vendor_id)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->get();
    
        return response()->json($sales);
    });

    //get sales from affiliate with dates filter

    Route::post('view/sales/affiliate','show_affiliate_sales_from_date');

    Route::post('view/sales/vendor','show_vendor_sales_from_date');

    Route::post('view/sales/vendor/as_affiliates','show_vendor_sales_from_date_as_affiliates');

  

    


    Route::get('view/user_sales_count/{id}', function ($id) {
        $sales_by_user = Sales::where('affiliate_id', $id)->get();

        $total_sales = 0;

        foreach( $sales_by_user as $sale){

            $commision = intval($sale->commission);
            $product_price = intval($sale->product_price);

            $income = ($commision/100)*$product_price ;
            $total_sales  =  $total_sales  + $income;
        }
       
        return response()->json(["count"=>count( $sales_by_user ), "aff"=>$id, "total_sales"=>strval($total_sales)]);
    });

// Get top 5 affiliates with the highest number of sales for a product within the current month
Route::get('top_affiliate/product/view/{product_id}', function ($product_id) {
    $firstDayOfMonth = now()->firstOfMonth();
    $current = now();

   /* $top_affiliates = Sales::where('product_id', $product_id)
        ->whereBetween('created_at', [$firstDayOfMonth, $lastDayOfMonth]) // Filter sales for the current month
        ->selectRaw('affiliate_id, count(*) as sales_count')
        ->groupBy('affiliate_id')
        ->orderBy('sales_count', 'desc')
        ->take(10) // Retrieve the top 5 affiliates
        ->get();

    // Retrieve affiliate details for each of the top affiliates
    foreach ($top_affiliates as $index => $affiliate) {
        $user = Members::where('affiliate_id', $affiliate->affiliate_id)->first();
        $top_affiliates[$index]->firstName = $user->firstName;
        $top_affiliates[$index]->lastName = $user->lastName;
    }*/


    $query = Sales::where('vendor_id', '16')
    ->selectRaw('sales.affiliate_id, COUNT(*) as count, members.*')
    ->join('members', 'members.affiliate_id', '=', 'sales.affiliate_id')
    ->groupBy('sales.affiliate_id', 'members.id', 'members.affiliate_id')
    ->where('sales.created_at', '>=', ($firstDayOfMonth))
    ->where('sales.created_at', '<=', $current)
    ->limit(10);


$sales_by_user = $query->orderBy('count', 'desc')->get();

return response()->json( $sales_by_user);


  //  return response()->json($top_affiliates);
});



    //get top affiliates for a vendor
    Route::get('top_affiliate/view/{vendor_id}', function ($vendor_id) {
        $top_affiliates = Sales::where('vendor_id', $vendor_id)->get();
    
        for($i=0; $i < count($top_affiliates); $i++){
    
            $user = Members::find($top_affiliates[$i]['affiliate_id']);
    
            $top_affiliates[$i]['affiliate_details'] = $user;
          //$vendors[$i]["image_path"] = asset('https://zenithstake.syncight.com/storage/images/vendor_images/' . $vendors[$i]["image"]);
    
        }
    
        return response()->json( $top_affiliates);
    });

    //vendor sales

    Route::get('view/vendor/sales/{vendor_id}', function ($vendor_id) {
        $vendor_sales = Sales::where('vendor_id', $vendor_id)->get();

        $total_sales = 0;

        foreach( $vendor_sales as $sale){

            $price = intval($sale->product_price);
            $product_price = intval($sale->product_price);
            $commision = intval($sale->commission);

            $aff_commission = ($commision/100)*$product_price ;

            $vendor_income = $price - $aff_commission;

            $total_sales  =  $total_sales  +  $vendor_income;
        }

        
    
        return response()->json($vendor_sales);
    });

    
  
});

//get vendor affiliates

Route::get('view/affiliates/{vendor_id}', function ($vendor_id) {

    $sales = Sales::where('vendor_id', $vendor_id)
    ->selectRaw('sales.affiliate_id, COUNT(*) as count, members.*')
    ->join('members', 'members.affiliate_id', '=', 'sales.affiliate_id')
    ->groupBy('sales.affiliate_id', 'members.id', 'members.affiliate_id')
    ->limit(200)
    ->get();


            return response()->json($sales);


});

Route::get('sales/today/duplicates/1', function () {
    $startDate = '2023-11-27'; // Replace with your start date
    $endDate = '2023-12-03';   // Replace with your end date

    // Retrieve sales records within the specified date range
    $sales = Sales::where("affiliate_id", "urmpAs")->
    
    whereBetween('created_at', [$startDate, $endDate])
        ->get();

    // Count occurrences of each customer_email
    $duplicateEmails = $sales->groupBy('customer_email')
        ->filter(function ($group) {
            return $group->count() > 1; // Filter customer emails that appear more than once
        })
        ->map(function ($group) {
            return $group->pluck('id'); // Retrieve IDs of sales with duplicated emails
        });

    return response()->json([
        "duplicate_sales_with_same_email" => $duplicateEmails,
    ]);
});

Route::get('sales/today/duplicates/2', function () {
    $startDate = '11/26/2023'; // Replace with your start date
    $endDate = '12/08/2023';   // Replace with your end date

    $sales = Sales::where("affiliate_id", "hAriNj")
        ->whereBetween('created_at', [$startDate, $endDate])
        ->get();

    // Extract emails from sales records
    $emails = $sales->pluck('customer_email');

    // Count occurrences of each email
    $emailCounts = $emails->countBy();


     // Filter emails with count greater than one
     $duplicatedEmails = $emailCounts->filter(function ($count) {
        return $count > 1;
    });

    // Sum the counts of duplicated emails
    $sumOfDuplicates = $duplicatedEmails->sum();

    return response()->json([
        "email_counts" => $emailCounts,
        "sum_of_duplicates" => $sumOfDuplicates,
    ]);
});

Route::get('sales/today/duplicates/2/{aff}', function ($aff) {
    $startDate = '2023-11-27'; // Replace with your start date
    $endDate = '2023-12-08';   // Replace with your end date

    $sales = Sales::where("affiliate_id", $aff)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->get();

    // Extract emails from sales records
    $emails = $sales->pluck('customer_email');

    // Count occurrences of each email
    $emailCounts = $emails->countBy();

    // Filter emails with count greater than one
    $duplicatedEmails = $emailCounts->filter(function ($count) {
        return $count > 1;
    });

    // Sum the counts of duplicated emails
    $sumOfDuplicates = $duplicatedEmails->sum();

    return response()->json([
        "duplicated_emails" => $duplicatedEmails,
        
        
        "sum_of_duplicates" => $sumOfDuplicates,

        "emails"=>$emailCounts,

        "count_email"=>count($emailCounts)
    ]);
});








Route::get('admin_sales/view', function () {

    $sales = Sales::all();

    $total_sales = 0;

    $total_revenue = 0;

    foreach($sales as $sale){
        $total_sales += intval($sale->product_price);


    }

    $startDateTime = Carbon::today(); // Get the start of today (12am)
        $endDateTime = Carbon::now(); // Get the current date and time
    
        $sales_today = Sales::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->get();


            $total_earnings_today = 0;
            foreach($sales_today as $sale_today){
                $total_earnings_today  += intval($sale_today->product_price);
        
        
            }
    


    return response()->json(["total_earnings"=>$total_sales,"sales_today"=>count($sales_today),"total_earnings_today"=>$total_earnings_today]);


});





    Route::get('notifications/all/', function () {

        $notifs =  Notification::all();

        

        return response()->json(["notifications"=>$notifs]);





    
});

//get user notifs

Route::get('notifications/user/{id}', function ($id) {

   // $notifs =  Notification::all();

    $notifs =  Notification::where('user_id', $id)->get();    

    return response()->json($notifs);






});


