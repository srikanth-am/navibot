@component('mail::message')
# Your request has been completed successfully! 

@component('mail::table')
| **Domain**                         | **Total Templates**         | **Unique URLs**        | **Not Working URLs**          |
| ---------------------------------- |:---------------------------:| :---------------------:| :----------------------------:|
| {{$data['domain']}}                | {{$data['total_template']}} | {{$data['total_url']}} |{{$data['not_working_urls']}}  |

@endcomponent
@component('mail::button', ['url' => url('/dashboard')])
Open
@endcomponent

Regards,<br>
<h1 style="color: #124265">
    Navi<span style="color: #d47c00">B</span>ot
</h1>
@endcomponent
