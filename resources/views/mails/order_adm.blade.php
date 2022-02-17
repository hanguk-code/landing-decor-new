<h1>Заказ № {{ $iddata }}</h1>
<p>Заказчик: {{ $fields['userForm']['name'] }}</p>
<p>Телефон:
	<a href="tel:{{ $fields['userForm']['phoneDetails']['formattedNumber'] }}">{{ $fields['userForm']['phoneDetails']['formattedNumber'] }}</a></p>
<p>Почта:
	<a href="mailto:{{ $fields['userForm']['email'] }}">{{ $fields['userForm']['email'] }}</a></p>
<p>Комментарий:
	{{ $fields['userForm']['comment'] }}
</p>
<table style="border: none; border-collapse: collapse;">
	<tbody>
		<tr>
			<th>№</th>
			<th>Изображение</th>
			<th>артикул</th>
			<th>Наименование товара</th>
			<th>Цена</th></tr>
			@php $inumb =0;@endphp
    @foreach($fields['cartData'] as $cart)
	@php $total += (int) str_replace(' ', '', $cart['price']); $inumb ++; @endphp
        <tr>
			<td>{{ $inumb }}</td>
				   <td><img src="https://decor-retro.ru/image/{{ $cart['image_url'] }}" width="100"/></td>
				<td>{{ $cart['article'] }}</td>
				<td><a href="https://decor-retro.ru{{ $cart['url'] }}">
						{{ $cart['name'] }}
				</a></td>
				<td>{{ $cart['price'] }}</td>     
        </tr>
    @endforeach	
            <tr><td colspan="3"></td>
			<td style="text-align:right">ИТОГО общая сумма заказа:</td>
			<td>{{ number_format($total, 0, '.', ' ') }}</td></tr> 
	</tbody>
</table> 
 <p>&nbsp;</p>
 <p>С уважением, ДЕКОР-РЕТРО. +7(985)272-77-80</p>
 <p><?php //echo $this->order->id;?></p>
 <p><?php /*print_r($data);*/?></p>


 


