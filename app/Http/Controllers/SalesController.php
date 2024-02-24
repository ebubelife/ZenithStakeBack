<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\Members;
use App\Models\Notification;
use App\Models\Products;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Mail\AffiliateEmail;
use App\Mail\VendorEmail;
use App\Mail\FinishReg;
use App\Mail\CourseAccess;
use App\Mail\Contest;
use App\Mail\MessageEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
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

    public function testemail(){

     /*   if(Mail::to("kongonut@gmail.com")->send(new AffiliateEmail( "zenithstake@gmail.com", "Affiliate", "10000","5000","Ebube Emeka","SMAC Course" ))){

            return true;

        }

        if(Mail::to("kongonut@gmail.com")->send(new VendorEmail("zenithstake@gmail.com","Vendor", "10000","4000","Ebube Emeka", "SMAC Course"))){

            return true;

        }*/

        if(Mail::to("kongonut@gmail.com")->send(new MessageEmail("ebubeemeka19@gmail.com"))){

            return true;

        }


       // if(Mail::to("zenithstake@gmail.com")->send(new Contest("Godspower"))){

          //  return true;

      //  }

        
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

        $naira_exchange_rate = DB::selectOne('SELECT value FROM settings WHERE settings_key = ? LIMIT 1', ['usd_to_naira']);

        $ghs_exchange_rate = DB::selectOne('SELECT value FROM settings WHERE settings_key = ? LIMIT 1', ['usd_to_ghs']);
    
        //

       // DB::beginTransaction();


        try{


            $validated = $request->validate([
            'vendor_id' => 'required|string',
            'affiliate_id' => 'required|string',
            'product_id' => 'required|string',
            'product_price' => 'required|string',
            'commission' => 'required|string',
            'tx_id' => 'required|string',
            'customer_name' => 'required|string',
            'customer_email' => 'required|string',
            'customer_phone' => 'required|string',
            'currency' => 'required|string',
           
        ]);

             $sale = new Sales();

             

            
             $sale->affiliate_id =  $validated["affiliate_id"];
             $sale->product_id =  $validated["product_id"];
             $sale->product_price =  $validated["product_price"];
             $sale->commission =  $validated["commission"];
        
             $sale->customer_name =  $validated["customer_name"];
             $sale->customer_email =  $validated["customer_email"];
             $sale->customer_phone =  $validated["customer_phone"];
             $sale->vendor_id =  $validated["vendor_id"];

            

           /// $characters = '0123456789abcdefghijklmnopqrstuvwxyz' ;
            //$random_string = substr(str_shuffle($characters), 0, 8);
            $sale->tx_id = $validated["tx_id"];

            $product = Products::where('id',$validated["product_id"])->first();

            $productName = $product->productName;
           

            //calculate total affiliate commission and save


            $affiliate = Members::where('affiliate_id', $validated["affiliate_id"])->first();

            $commission_int = intval($validated["commission"]);


            $price_int = $validated["currency"]=="USD"?(intval($validated["product_price"]) * (intval($naira_exchange_rate->value))):(intval($validated["product_price"]));

        



            $total_aff_sales = intval($affiliate->total_aff_sales_cash);
            $total_aff_sales_num = intval($affiliate->total_aff_sales);

            $affiliate->total_aff_sales_cash = strval((($commission_int/100) * $price_int)  + $total_aff_sales);
            $affiliate->total_aff_sales = strval($total_aff_sales_num + 1);

            $unpaid_balance_affiliate = intval($affiliate->unpaid_balance);

            $affiliate->unpaid_balance = strval($unpaid_balance_affiliate + (($commission_int/100) * $price_int));

            $timestamp = time(); // Get the current Unix timestamp
            $timestamp_format = date('Y-m-d H:i:s', $timestamp); // Convert to the timestamp format

            $affiliate->last_sale_time = $timestamp_format ;
            $affiliate->last_sale_amount = strval((($commission_int/100) * $price_int)) ;
            $affiliate->last_sale_product = $validated["product_id"] ;



           


            //calculate vendor commision an save


            $user = Members::where('id', $validated["vendor_id"])->first();

            $commission_int = intval($validated["commission"]);
            $price_int = intval($validated["product_price"]);

            $aff_commision = (($commission_int/100) * $price_int);
            $zenithstake_commision = ((10/100) * $price_int);

            $vendor_comission = ($price_int - $aff_commision) - $zenithstake_commision;

            $total_vendor_sales = intval($user->total_vendor_sales_cash);
            $total_vendor_sales_num = intval($user->total_vendor_sales);

            $unpaid_balance_vendor = intval($user->unpaid_balance_vendor);

            $user->total_vendor_sales_cash = strval($vendor_comission + $total_vendor_sales);
            $user->total_vendor_sales = strval($total_vendor_sales_num + 1);

            $user->unpaid_balance_vendor = strval($unpaid_balance_vendor + ($vendor_comission ));

            //check if sale already exists with same customer email
            $check_c_email_record = Sales::where('customer_email', $validated["customer_email"])
            ->where('affiliate_id', $validated["affiliate_id"]) // Adding condition for affiliate_id
            ->where('product_id', "1")
            ->first();
        

            if(!$check_c_email_record){

                if( $sale->save()){


                    $new_notif = new Notification();
    
    
                    $new_notif->type = "NEW_SALE";
    
                    $new_notif->header = "New Sale!";
                    $new_notif->body = "congratulations! You have made a new sale for the product - ".$productName;
    
                    $new_notif->save();
    
                //Save affiliate commission
    
              if( $affiliate->save()){
                if($validated["product_id"] == "1"){
    
                   
                    Mail::to($validated["customer_email"])->send(new FinishReg($validated["customer_name"],$sale->id));
                  
                }
    
                else{
    
                 
    
                    Mail::to($validated["customer_email"])->send(new CourseAccess($validated["customer_name"], $product->ProductTYLink, $productName ));
                   
    
                }
    
              
                    //Save vendor commission
               if( $user->save()){
    
                    $getAffiliate = Members::where('affiliate_id', $validated["affiliate_id"])->first();
                    $getVendor = Members::where('id', $validated["vendor_id"])->first();
    
                    
       
                      
            
                  
                    //send email to affiliate
    
                    Mail::to($getAffiliate->email )->send(new AffiliateEmail( $getAffiliate->email, $getAffiliate->firstName, $validated["product_price"]/$naira_exchange_rate->value,$aff_commision/$naira_exchange_rate->value, $validated["customer_name"], $productName));
    
                    Mail::to($getVendor->email )->send(new VendorEmail($getVendor->email,$getVendor->firstName,$validated["product_price"],$vendor_comission/$naira_exchange_rate->value,$validated["customer_name"],$productName));
    
                  /*  if(Mail::to($getAffiliate->email )->send(new AffiliateEmail( $getAffiliate->email, $getAffiliate->firstName, $validated["product_price"],strval($aff_commision ), $validated["customer_name"], $productName))){
    
                    
    
                        //send email to vendor
    
                                if(Mail::to("ebubeemeka19@gmail.com")->send(new VendorEmail( "ebubeemeka19@gmail.com",$getVendor->firstName,$validated["product_price"],strval($vendor_comission),$validated["customer_name"],$productName))){
    
                                    return response()->json(['message'=>'Successful' ],200);
    
                                }
                                else{
    
                                    return response()->json(['message'=>'Successful. Could not send email notification - 1'],200);
                                }
    
                }else{
                    return response()->json(['message'=>'Successful!.Could not send email notification - 2'],200);
                }*/
    
                return response()->json(['message'=>'Successful!'],200);
    
               
    
               }else{
                return response()->json(['message'=>'Could not verify the vendor. Please contact Zenithstake admin'],405);
               }
            }
            else{
                return response()->json(['message'=>'Could not verify the affiliate. Please contact the ZenithStake admin'],405);
            }
    
        }else{
            return response()->json(['message'=>'Could not save this transaction. Please contact the ZenithStake admin'],405);
    
        }

                
            }else{

                return response()->json(['message'=>'Sorry! Your email has been previously employed on a sale. Please contact admin'],405);

            }

            

       // DB::commit();
    }
    catch (\Illuminate\Validation\ValidationException $exception) {
        $errors = $exception->errors();
    
        return response()->json(['message' => 'Validation error', 'errors' => $errors], 422);
      } catch (\Exception $e) {
        return response()->json(['message' => 'An error occurred, please try again', 'error' => $e->getMessage()], 405);
      }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sales  $sales
     * @return \Illuminate\Http\Response
     */
    public function show(Sales $sales)
    {
        $sales = Sales::join('products', 'sales.product_id', '=', 'products.id')
        ->join('members', 'sales.affiliate_id', '=', 'members.affiliate_id')
        ->select('sales.*', 'products.productName as product_name', 'products.productPrice', 'members.firstName as affiliate_first_name','members.lastName as affiliate_last_name')
        ->orderByDesc('created_at')
        ->get();

    return response()->json($sales);
    }


    public function show_affiliate_sales_from_date(Request $request){

      

      /*  $sale = new Sales();

            
        $sale->affiliate_id =  $validated["affiliate_id"];
        $sale->from_date =  $validated["from_date"];
        $sale->to_date =  $validated["to_date"];*/


        try{

            $validated = $request->validate([
                'affiliate_id' => 'required|string',
                'from_date' => 'required|string',
                'to_date' => 'required|string',
                'selected_product' => 'required|string',
               
               
            ]);

        $from = $validated["from_date"];
        $to = $validated["to_date"];

        if($validated["selected_product"] == 'all'){
            $sales_by_user = Sales::where('affiliate_id', $validated["affiliate_id"])
          //  ->where('created_at', '>=', Carbon::parse($from))
         //   ->where('created_at', '<=', Carbon::parse($to))

            ->whereBetween('created_at', [Carbon::parse($from)->startOfDay()->addDay(), Carbon::parse($to)->endOfDay()->addDay() ])
      
            ->get();

        }else{

            $sales_by_user = Sales::where('affiliate_id', $validated["affiliate_id"])
         //   ->where('created_at', '>=', Carbon::parse($from))
         //  ->whereBetween('created_at', [Carbon::parse($from), Carbon::parse($to)])
         ->whereBetween('created_at', [Carbon::parse($from)->startOfDay()->addDay(), Carbon::parse($to)->endOfDay()->addDay() ])
      
            ->where('created_at', '<=', Carbon::parse($to))
            ->where('product_id', $validated["selected_product"])
            ->get();

        }
      


                        return response()->json(["message"=>$sales_by_user, "to"=>$to, "from"=>$from]);

        }
        catch(\Exception $e){
            return response()->json(['message'=>'An error occured, please try again', 'error'=>$e],405);
    
    
        }

    }



    public function show_vendor_sales_from_date(Request $request){

      

        /*  $sale = new Sales();
  
              
          $sale->affiliate_id =  $validated["affiliate_id"];
          $sale->from_date =  $validated["from_date"];
          $sale->to_date =  $validated["to_date"];*/
  
  
          try{
  
              $validated = $request->validate([
                  'vendor_id' => 'required|string',
                  'from_date' => 'required|string',
                  'to_date' => 'required|string',
                  'selected_product' => 'required|string',
                 
                 
              ]);
  
          $from = $validated["from_date"];
          $to = $validated["to_date"];
  
          if($validated["selected_product"] == 'all'){
              $sales_by_user = Sales::where('vendor_id', $validated["vendor_id"])
              ->where('created_at', '>=', Carbon::parse($from)->startOfDay()->addDay())
              ->where('created_at', '<=', Carbon::parse($to)->endOfDay()->addDay() )
              ->get();
  
          }else{
  
            $sales_by_user = Sales::where('vendor_id', $validated["vendor_id"])
              ->where('created_at', '>=', Carbon::parse($from)->startOfDay())
              ->where('created_at', '<=', Carbon::parse($to)->endOfDay())
              ->where('product_id', $validated["selected_product"])
              ->get();
  
          }
        
  
  
                          return response()->json(["message"=>$sales_by_user, "to"=>$to, "from"=>$from]);
  
          }
          catch(\Exception $e){
              return response()->json(['message'=>'An error occured, please try again', 'error'=>$e],405);
      
      
          }
  
      }


      public function show_vendor_sales_from_date_as_affiliates(Request $request)
      {
          try {
              $validated = $request->validate([
                  'vendor_id' => 'required|string',
                  'from_date' => 'required|string',
                  'to_date' => 'required|string',
                  'selected_product' => 'required|string',
              ]);
      
              $from = $validated["from_date"];
              $to = $validated["to_date"];
      
              $query = Sales::where('vendor_id', $validated["vendor_id"])
                  ->selectRaw('sales.affiliate_id, COUNT(*) as count, members.*')
                  ->join('members', 'members.affiliate_id', '=', 'sales.affiliate_id')
                  ->groupBy('sales.affiliate_id', 'members.id', 'members.affiliate_id')
                  ->where('sales.created_at', '>=', Carbon::parse($from)->startOfDay()->addDay())
                  ->where('sales.created_at', '<=', Carbon::parse($to)->endOfDay()->addDay())
                  ->limit(1000);
      
              if ($validated["selected_product"] !== 'all') {
                  $query->where('sales.product_id', $validated["selected_product"]);
              }
      
              $sales_by_user = $query->orderBy('count', 'desc')->get();
      
              return response()->json(["message" => $sales_by_user, "to" => $to, "from" => $from]);
          } catch (\Exception $e) {
              return response()->json(['message' => 'An error occurred, please try again', 'error' => $e->getMessage()], 405);
          }
      }
      
  
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sales  $sales
     * @return \Illuminate\Http\Response
     */
    public function edit(Sales $sales)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sales  $sales
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sales $sales)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sales  $sales
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sales $sales)
    {
        //
    }
}
