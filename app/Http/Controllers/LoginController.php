<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Hyn\Tenancy\Models\Hostname;

class LoginController extends Controller
{
    public function index(){
        return view('auth.domain');
    }

    public function routeToTenant( Request $request ) {        
        $invalidSubdomains = config( 'app.invalid_subdomains' );
        $validatedData = $request->validate([
            'account' => [
                'required', 
                'string',
                Rule::notIn( $invalidSubdomains ),
                'regex:/^[A-Za-z0-9](?:[A-Za-z0-9\-]{0,61}[A-Za-z0-9])$/'
            ],
        ]);

        $fqdn = $validatedData['account'] . '.' . config( 'app.url_base' );
        $hostExists = Hostname::where( 'fqdn', $fqdn )->exists();
        $port = $request->server('SERVER_PORT') == 8000 ? ':8000' : '';
        if ( $hostExists ) {
            return redirect( ( $request->secure() ? 'https://' : 'http://' ) . $fqdn . $port . '/login');
        } else {
            echo "Your domain name does not exist, contact the administrator";
            exit();
            //return redirect('register')
            //->withInput();
        }
    }

}
