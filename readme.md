# Filtered Class Loader

A class to handle the dynamic loading of a class that's loaded using a filter. Many plugins in WP allow you to 
filter what class is loaded for a function within that plugin. It's common in WooCommerce to do this to 
replace things like the data-store or logger classes. This collection of methods allows you to replace the class
set to load on the filter, and still provide you with a way of extending whatever class was due to be loaded. 

A typical example would look like this:
```php
$class_name = apply_filters( 'filter_name', 'DefaultClassToReturn' );
$class = new $class_name();
```

This helper is useful when you don't know (or care) what class is being loaded but you want to inject some logic
to one of the methods in that class before allowing the parent method to run. Typically you'd extend the class, but
because the class is loaded dynamically it's not possible to extend it. This helper allows you to create an alias
and then extend that.

The name of existing methods to override is usually known because the class that's being loaded is usually a 
class that extends an abstract defined by the plugin. Even still, care should be taken to ensure that the new
functionality is compatible with any antcipiated changes to the parent class.

## Usage 
Usage:
```php
Filtered_Class_Loader::create( 
    'wp_filter_that_modifies_class', // the filter name.
    'DefaultClassToReturn', // the default value of the filter.
    'MyNewClass', // the name you intend to use to define your new class
    'ExtendableClass' // the name of the class that will be extended by the new class.
);

// Now we can define our class that extends the original class.
class MyNewClass extends ExtendableClass {
   public function some_method() {
        // Do something before the parent method is called.
        parent::some_method();
        // Do something after the parent method is called.
    }
}
```


## Example Scenario
A use case for this would be if you needed to add additional logging to a method in a class, that's 
defined by another plugin. For example, the below code is an abstract class for a payment-gateway in
an ecommerce application.
```php
/**
 * ECommerceApplication.php
 */

// The ecommerce application will define an abstract payment gateway class.
abstract class Payment_Gateway {
	public function process_payment() {
		// process the payment.
	}
}

// The ecommerce application will then load the payment gateway using a filter.
class ECommerceApplication { 
	// ...

	public function load_payment_gateway() {
		$class_name = apply_filters( 'payment_gateway', 'Default_Payment_Gateway' );
		$payment_gateway = new $class_name();
	}
	
	// ...
}
```

The plugin that defines the payment gateway is unknown, but we know that the payment gateway will 
extend the abstract class. A plugin might define itselft as the payment gateway using a filter like this: 
```php
/**
 * ThirdPartyPaymentGatewayPlugin.php
 */
class My_Payment_Gateway extends Payment_Gateway {
	public function process_payment() {
		// process the payment.
	}
}

add_filter( 'payment_gateway', 
	function() {
		return 'My_Payment_Gateway';
	}
);
```

We're happy with the way the payment gateway works, but we want to add some additional logging to the
`process_payment` method. We can do this by using the `Filtered_Class_Loader` to extend the unknown class. 
```php
/**
 * MyAwesomeCode.php
 */ 

Filtered_Class_Loader::create( 
	'payment_gateway', // the filter name.
	'Default_Payment_Gateway', // the default value of the filter.
	'My_Payment_Gateway', // the name you intend to use to define your new class
	'Extendable_Unknown_Payment_Gateway' // the name of the class that will be extended by the new class.
);

// Now we can define our class that extends the original class.
class My_Logging_Gateway extends Extendable_Unknown_Payment_Gateway {
	public function process_payment() {
		$this->log( 'Payment processing started.' );
		parent::process_payment();
		$this->log( 'Payment processing completed.' );
	}
}
```
By using the `Filtered_Class_Loader` we can ensure that our new class extends whatever class is defined by any other
plugin/code, inject our logic and continue to use the existing functionality.