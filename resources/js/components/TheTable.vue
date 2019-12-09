<template>
<div class="table-responsive">
    <table class="table table-bordered table-striped table-sm">
        <thead>
            <tr>
                <th>Grado</th>
                <th>Altura de olas [m]</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>0</td>
                <td>Sin olas</td>
                <td>Mar llana o en calma</td>
            </tr>
            <tr>
                <td>1</td>
                <td>0.00 - 0.10</td>
                <td>Mar rizada</td>
            </tr>
            <tr>
                <td>2</td>
                <td>0.10 - 0.50</td>
                <td>Marejadilla</td>
            </tr>
            <tr>
                <td>3</td>
                <td>0.50 - 1.25</td>
                <td>Marejada</td>
            </tr>
            <tr>
                <td>4</td>
                <td>1.25 - 2.50</td>
                <td>Fuerte marejada</td>
            </tr>
            <tr>
                <td>5</td>
                <td>2.50 - 4.00</td>
                <td>Gruesa</td>
            </tr>
            <tr>
                <td>6</td>
                <td>4.00 - 6.00</td>
                <td>Muy gruesa</td>
            </tr>
            <tr>
                <td>7</td>
                <td>6.00 - 9.00</td>
                <td>Arbolada</td>
            </tr>
            <tr>
                <td>8</td>
                <td>9.00 - 14.0</td>
                <td>Montañosa</td>
            </tr>
            <tr>
                <td>9</td>
                <td>Mas de 14.0</td>
                <td>Enorme</td>
            </tr>
        </tbody>
    </table>
</div>
</template>

<script>
    export default {
        data() {
            return {

            }
        },
    }
</script>

<style scoped>
table {
    width: 400px;
    color: #0e8cb3;
    position: absolute;
    z-index: 50;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 6px rgba(57,73,76,0.35);
}
table thead th {
    color: black;
}

@media only screen and (max-width: 600px) {
table {
    width: 320px;
    left: 20px;
}

}

</style>
