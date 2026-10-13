<?php
/**
 * Central definition of every category: display info + default columns
 * used when a brand new table is created (new_table.php).
 * column_type: text | number | date | currency
 */
function tc_category_map() {
    return [
        'student_record' => [
            'label' => 'Student Record',
            'icon'  => 'bi-mortarboard-fill',
            'desc'  => 'Roll numbers, names, class, subjects, marks and attendance.',
            'is_commercial' => 0,
            'columns' => [
                ['name' => 'Roll No',        'type' => 'number'],
                ['name' => 'Student Name',   'type' => 'text'],
                ['name' => 'Class',          'type' => 'text'],
                ['name' => 'Subject',        'type' => 'text'],
                ['name' => 'Marks',          'type' => 'number'],
                ['name' => 'Attendance %',   'type' => 'number'],
            ],
        ],
        'shop_order' => [
            'label' => 'Shop Order',
            'icon'  => 'bi-cart-fill',
            'desc'  => 'Customer orders with quantity, price and auto-calculated total.',
            'is_commercial' => 0,
            'columns' => [
                ['name' => 'Order ID',       'type' => 'text'],
                ['name' => 'Customer Name',  'type' => 'text'],
                ['name' => 'Product',        'type' => 'text'],
                ['name' => 'Quantity',       'type' => 'number'],
                ['name' => 'Price',          'type' => 'currency'],
                ['name' => 'Total',          'type' => 'currency', 'formula' => 1],
                ['name' => 'Order Date',     'type' => 'date'],
            ],
        ],
        'employee_info' => [
            'label' => 'Employee Info',
            'icon'  => 'bi-person-badge-fill',
            'desc'  => 'Employee directory with department, designation and salary.',
            'is_commercial' => 0,
            'columns' => [
                ['name' => 'Employee ID',    'type' => 'text'],
                ['name' => 'Full Name',      'type' => 'text'],
                ['name' => 'Department',     'type' => 'text'],
                ['name' => 'Designation',    'type' => 'text'],
                ['name' => 'Joining Date',   'type' => 'date'],
                ['name' => 'Salary',         'type' => 'currency'],
            ],
        ],
        'library_books' => [
            'label' => 'Library Book List',
            'icon'  => 'bi-book-fill',
            'desc'  => 'Track book issues, returns and availability status.',
            'is_commercial' => 0,
            'columns' => [
                ['name' => 'Book ID',        'type' => 'text'],
                ['name' => 'Title',          'type' => 'text'],
                ['name' => 'Author',         'type' => 'text'],
                ['name' => 'Category',       'type' => 'text'],
                ['name' => 'Issue Date',     'type' => 'date'],
                ['name' => 'Return Date',    'type' => 'date'],
                ['name' => 'Status',         'type' => 'text'],
            ],
        ],
        'product_stock' => [
            'label' => 'Product Stock',
            'icon'  => 'bi-box-seam-fill',
            'desc'  => 'Inventory levels with auto-calculated stock value.',
            'is_commercial' => 0,
            'columns' => [
                ['name' => 'Product ID',        'type' => 'text'],
                ['name' => 'Product Name',      'type' => 'text'],
                ['name' => 'Category',          'type' => 'text'],
                ['name' => 'Quantity',          'type' => 'number'],
                ['name' => 'Price',             'type' => 'currency'],
                ['name' => 'Stock Value',       'type' => 'currency', 'formula' => 1],
            ],
        ],
        'commercial' => [
            'label' => 'Commercial Category',
            'icon'  => 'bi-cash-coin',
            'desc'  => 'Invoices / billing sheets with quantity × price − discount% math.',
            'is_commercial' => 1,
            'columns' => [
                ['name' => 'Item',           'type' => 'text'],
                ['name' => 'Quantity',       'type' => 'number'],
                ['name' => 'Price',          'type' => 'currency'],
                ['name' => 'Discount %',     'type' => 'number'],
                ['name' => 'Total',          'type' => 'currency', 'formula' => 1],
            ],
        ],
        'custom' => [
            'label' => 'Custom Category Editor',
            'icon'  => 'bi-sliders',
            'desc'  => 'Start from a blank table and define your own columns.',
            'is_commercial' => 0,
            'columns' => [
                ['name' => 'Column 1',       'type' => 'text'],
                ['name' => 'Column 2',       'type' => 'text'],
            ],
        ],
    ];
}
