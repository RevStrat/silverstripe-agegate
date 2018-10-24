# SilverStripe AgeGate

This module provides an age-gate function, with support for the IPStack API or GeoLite2 database. Please provide your own GeoLite2 database in the ```data``` folder.

### Installation

composer require revstrat/silverstripe-agegate

### Usage

Place ```<% include AgeGate %>``` just inside your opening ```<body>``` tag.

Normal template inheritance allows for customization of the age gate appearance.

Configure the desired GeoIP service by setting the following in your app's yml file:

```
RevStrat\AgeGate\PageControllerExtension:
  geoip_source: "RevStrat\\AgeGate\\IPStack"
```

Where ```RevStrat\\AgeGate\\IPStack``` is the namespaced class that will handle GeoIP lookups. To create your own, simply implement the ```GeoIPServiceInterface``` interface and update ```geoip_source``` to your fully namespaced class.

The IPStack interface makes use of two environment variables:

* IPSTACK_ENDPOINT="http://api.ipstack.com/%s?access_key=%s"
* IPSTACK_ACCESS_KEY="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"

The IPSTACK_ENDPOINT key points to the endpoint URL and is parsed with sprintf. Update this to a compatible service (such as Nekudo) or change the protocol (paid accounts on IPStack can use https for lookups). The IPSTACK\_ACCESS\_KEY should be set to your access key.

Once everything is configured, be sure to run ```/dev/build?flush=all```.

Configure age restriction by country under Settings > Age Gate Control.

## Overiding the form

Implement these methods on your PageController. These methods are an example of how one might use two buttons on the age gate.

```
public function updateGetShowAgeGate(&$ageGateActive, &$sufficientAge) {
        
    }

    public function updateAgeGateForm(&$fields, &$actions, $minimumAge) {
    	$fields->removeByName('OfAge');
    	$actions = new FieldList(
    		FormAction::create('passAgeGate')->setTitle("Yes I'm over $minimumAge"),
    		FormAction::create('failAgeGate')->setTitle("I'm not $minimumAge yet")
    	);
    }

    public function passAgeGate($data, Form $form) {
    	$data['OfAge'] = true;
    	return $this->doAgeGate($data, $form);
    }

    public function failAgeGate($data, Form $form) {
    	$data["OfAge"] = false;
    	return $this->doAgeGate($data, $form);
    }
```