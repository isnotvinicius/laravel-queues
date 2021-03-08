<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Jobs\SendMailJob;
use App\Mail\SendMailable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function sendEmail()
    {
        $emailJob = (new SendMailJob())->delay(Carbon::now()->addSeconds(1));
        dispatch($emailJob);
        echo 'email sent';
    }
}
