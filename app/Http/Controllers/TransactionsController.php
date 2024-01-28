<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use App\Models\Members;
use Illuminate\Http\Request;
use App\Mail\AffiliatePayment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class TransactionsController extends Controller
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


         public function pay_vendors(Request $request){

            $naira_exchange_rate = DB::selectOne('SELECT value FROM settings WHERE settings_key = ? LIMIT 1', ['usd_to_naira']);

            $ghs_exchange_rate = DB::selectOne('SELECT value FROM settings WHERE settings_key = ? LIMIT 1', ['usd_to_ghs']);
        

        $all_tx_result = array();

        try{

              //get affiliate

              $unpaid_users = Members::where("is_vendor", true)
              ->where("payment_reference_paystack","!=",null)
            //  ->where("id","!=",16)
              ->where("withdrawal_settings",true)
             // ->whereIn("email", [ "ebubeemeka19@gmail.com","aimchinaza3039@gmail.com" ])
              ->whereRaw("CAST(unpaid_balance_vendor AS UNSIGNED) > 200")
             // ->where("id",34 )
              ->get();




            foreach( $unpaid_users as  $unpaid_user){

                $amount = $unpaid_user->unpaid_balance_vendor;

          
            $url = "https://api.flutterwave.com/v3/transfers";

            $fields = array(
              "account_bank" => $unpaid_user->bank,
              "amount" =>$amount,
             
              "account_number"=> $unpaid_user->bank_account_number,
              "narration" => "ZENITHSTAKE ENTERPRISE",
              "currency"=> "NGN",
            );

          
          
         /*   $fields_string = http_build_query($fields);
          
            //open connection
            $ch = curl_init();
            
            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              "Authorization: Bearer TOKEN HERE",
              "Cache-Control: no-cache",
            ));
            
            //So that curl_exec returns the contents of the cURL; rather than echoing it
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);*/

            $jsonData = json_encode($fields);

            $curl = curl_init();
            
         
                curl_setopt_array($curl, [
                    CURLOPT_URL => 'https://api.flutterwave.com/v3/transfers',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $jsonData,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer '.env('FLW_API_KEY'),
                        'Content-Type: application/json',
                    ],
                ]);
            
            //execute post
            $result = curl_exec($curl);
           // echo $result;

           $res = json_decode($result, true);

           if($res["status"]=="success"){

            $update_user = Members::find($unpaid_user->id);

            $update_user->unpaid_balance_vendor= "0.00";

            $update_user->save();

            $tx= new Transactions();
            $tx->tx_ref = $res["data"]["reference"];
            $tx->tx_type = "VENDOR_PAYMENT";
            $tx->user_id =  $unpaid_user->id;

            $tx->amount = $amount ;
            $tx->status = "DONE";
    
           if($tx->save()){


            Mail::to($unpaid_user->email)->send(new AffiliatePayment( intval($unpaid_user->unpaid_balance_vendor)/intval($naira_exchange_rate->value),$unpaid_user->firstName." ".$unpaid_user->lastName));
            $single_tx_result = array("user"=>$unpaid_user->id,"result"=>$res); 

            array_push($all_tx_result, $single_tx_result);

           }


        }
        else{

            $tx= new Transactions();
            $tx->tx_ref = "TX_NOT_FOUND";
            $tx->tx_type = "AFFILIATE_PAYMENT";
            $tx->user_id =  $unpaid_user->id;

            $tx->amount = $amount ;
            $tx->status = "FAILED";
        }
        

        }

           return response()->json(['message'=> "done","tx_result"=>$all_tx_result],200);
       
    }
    catch(\Exception $e){
        return response()->json(['message'=>'An error occured, please try again', 'error'=>$e],405);







    }
}


    public function pay_affiliates(Request $request){

        $all_tx_result = array();
        
        $naira_exchange_rate = DB::selectOne('SELECT value FROM settings WHERE settings_key = ? LIMIT 1', ['usd_to_naira']);

        $ghs_exchange_rate = DB::selectOne('SELECT value FROM settings WHERE settings_key = ? LIMIT 1', ['usd_to_ghs']);
    

        try{

              //get affiliate

              $unpaid_users = Members::where("is_vendor", false)
            //  ->where("affiliate_id","=" ,"DN3jYz")
             // -> where ("id", "=",3642)
              ->where("payment_reference_paystack","!=",null)
              ->where("email", "muhammadthalithmuneerah591@gmail.com")
            // ->where("withdrawal_settings",true)
             // ->whereIn("email", [ "ebubeemeka19@gmail.com","aimchinaza3039@gmail.com" ])
              ->whereRaw("CAST(unpaid_balance AS UNSIGNED) > 200")
              ->get();




            foreach( $unpaid_users as  $unpaid_user){

                $amount = $unpaid_user->unpaid_balance;

          
            $url = "https://api.flutterwave.com/v3/transfers";

            $fields = array(
              "account_bank" => $unpaid_user->bank,
              "amount" =>$amount,
             
              "account_number"=> $unpaid_user->bank_account_number,
              "narration" => "ZENITHSTAKE ENTERPRISE",
              "currency"=> "NGN",
            );

          
          
         /*   $fields_string = http_build_query($fields);
          
            //open connection
            $ch = curl_init();
            
            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              "Authorization: Bearer TOKEN HERE",
              "Cache-Control: no-cache",
            ));
            
            //So that curl_exec returns the contents of the cURL; rather than echoing it
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);*/

            $jsonData = json_encode($fields);

            $curl = curl_init();
            
         
                curl_setopt_array($curl, [
                    CURLOPT_URL => 'https://api.flutterwave.com/v3/transfers',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $jsonData,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer '.env('FLW_API_KEY'),
                        'Content-Type: application/json',
                    ],
                ]);
            
            //execute post
            $result = curl_exec($curl);
           // echo $result;

           $res = json_decode($result, true);

           if($res["status"]=="success"){

            $update_user = Members::find($unpaid_user->id);

            $update_user->unpaid_balance= "0.00";

            $update_user->save();

            $tx= new Transactions();
            $tx->tx_ref = $res["data"]["reference"];
            $tx->tx_type = "AFFILIATE_PAYMENT";
            $tx->user_id =  $unpaid_user->id;

            $tx->amount = $amount ;
            $tx->status = "DONE";
    
           if($tx->save()){


            Mail::to($unpaid_user->email)->send(new AffiliatePayment( intval($unpaid_user->unpaid_balance)/intval($naira_exchange_rate->value),$unpaid_user->firstName." ".$unpaid_user->lastName));
            $single_tx_result = array("user"=>$unpaid_user->id,"result"=>$res); 

            array_push($all_tx_result, $single_tx_result);

           }


        }
        else{

            $tx= new Transactions();
            $tx->tx_ref = "TX_NOT_FOUND";
            $tx->tx_type = "AFFILIATE_PAYMENT";
            $tx->user_id =  $unpaid_user->id;

            $tx->amount = $amount ;
            $tx->status = "FAILED";
        }
        

        }

           return response()->json(['message'=> "done","tx_result"=>$all_tx_result],200);
       
    }
    catch(\Exception $e){
        return response()->json(['message'=>'An error occured, please try again', 'error'=>$e],405);







    }
}


    public function store(Request $request)
    {
        //


        try{

           

            $validated = $request->validate([
                'tx_ref' => 'required|string',
                'tx_type' => 'required|string',
                'user_id'=>  'required|string',
                'amount'=>  'required|string',
                'status'=>  'required|string',
               
               
            ],
           
        );
    
       
   
        
        $tx= new Transactions();
        $tx ->tx_ref = $validated['tx_ref'];
        $tx ->tx_type = $validated['tx_type'];
        $tx ->user_id =  $validated['user_id'];

       if($tx->save()){

        $user = Members::where('id', $request->user_id)->first();

        $user->is_payed = "true";

        if($user->save()){

            //if transaction is successful and tx type is VENDOR_REG send email to vendor to verify email

            if($validated['tx_type'] == 'VENDOR_REG' || $validated['tx_type'] == 'AFFILIATE_REG' ){
                  
            if(Mail::to($user->email)->send(new ConfirmEmail( $user->email_code,$user->firstName))){

                return true;

            }

            }

            return response()->json(['message'=>'Transaction successfully saved'],200);

        }
        else{
            return response()->json(['message'=>'An error occured, please contact support', 'error'=>$e],405);
    
        }

       

       }
      
    

       else
       return response()->json(['message'=>'An error occured, please contact support', 'error'=>$e],405);
    

       

    
             
        }
        catch(\Exception $e){
            return response()->json(['message'=>'An error occured, please try again', 'error'=>$e],405);
    
    
        }
    


    }


    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function show(Transactions $transactions)
    {
        //
        $transactions = Transactions::all();
        return response()->json($transactions);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function edit(Transactions $transactions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transactions $transactions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transactions $transactions)
    {
        //
    }
}
