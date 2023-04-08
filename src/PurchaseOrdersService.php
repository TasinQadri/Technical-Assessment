<?php

declare(strict_types=1);

namespace Yarikul;

use Doctrine\DBAL\Connection;

class PurchaseOrdersService
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return iterable<PurchaseOrder>
     */
    public function list(Filters $filters): iterable
    {
        $query = 'SELECT * FROM purchase_orders WHERE 1=1';
        $parameters = [];

        foreach ($filters as $key => $value) {
            if ('' === $value || null === $value) {
                continue;
            }

            // not using concatenation to make it easy to read for non-php developers
            $query = " {$query} AND {$key} = :{$key}";
            $parameters[$key] = $value;
        }

        $query = "{$query} ORDER BY date ASC, product ASC";

        foreach ($this->connection->iterateAssociative($query, $parameters) as $row) {
            yield new PurchaseOrder(
                date: new \DateTimeImmutable($row['date']),
                supplier: $row['supplier'],
                orderNumber: $row['order_number'],
                product: $row['product'],
                pricePerTonne: \round(($row['price_per_100_grams'] ?? 0) * 10 * 1000, 3)
            );
        }
    }

    /**
     * @return iterable<PurchaseOrder>
     */
    public function averageProductPricePerTonnePerYear(Filters $filters): iterable
    {
        $averageProduct = "select date,  strftime('%y',date) as yr, supplier, product, ord, sum((price_per_100_grams * 10 / 1000) * (quantity_in_kg / 1000))/sum(quantity_in_kg / 1000) 
         from purchase_orders where 1=1";

        $parameters = [];
        for ($i = 0; $i < $filters; $i++){
            $averageProduct .= " AND strftime('%Y',{$i}) = :{$i}";
            $parameters[$i] = $filters[$i];
        }

        $averageProduct .= " GROUP BY  strftime('%Y', date),product";

        $records = $this->connection->fetchAllAssociative($averageProduct);

        while ($records) {
           $obj =  new PurchaseOrder(
                 $records['date'],
                $records['supplier'],
                $records['order_number'],
                $records['product_price'],
            );
        }
        return [];
    }
}