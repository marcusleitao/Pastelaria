<!DOCTYPE html>
<html>
<head>
    <title>Pedido Realizado</title>
</head>
<body>
    <h1>Seu pedido foi realizado com sucesso!</h1>
    <table width="600" border="1">
        <tr>
            <td colspan="4">
                <h2>Detalhes do Pedido</h2>
                <b>ID do Pedido:</b> {{ $order->id }}<br>
                <b>Data do Pedido:</b> {{ $order->created_at->format('d/m/Y H:i') }}<br>
            </td>
        </tr>
        <tr>
            <td><b>Nome</b></td>
            <td><b>Preço</b></td>
            <td><b>Quantidade</b></td>
            <td><b>Total</b></td>
        </tr>
        @foreach ($order->products as $product)
            <tr>
                <td>{{ $product->nome }}</td>
                <td>{{ 'R$ ' . number_format($product->preco, 2, ',', '.') }}</td>
                <td>{{ $product->pivot->quantity }}</td>
                <td>{{ 'R$ ' . number_format($product->preco * $product->pivot->quantity, 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>