<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\LivewirePayload;
use LaraDumps\LaraDumps\Support\{Dumper, IdeHandle};
use ReflectionClass;

class LivewireComponentsObserver
{
    public function register(): void
    {
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::listen('view:render', function (View $view) {
                if (!$this->isEnabled()) {
                    return;
                }

                $component = $view->getData()['_instance'];

                if (in_array(get_class($component), (array) (config('laradumps.ignore_livewire_components')))) {
                    return;
                }

                $properties = $component->getPublicPropertiesDefinedBySubClass() +
                    $component->getProtectedOrPrivatePropertiesDefinedBySubClass();

                $data = [
                    'data' => Dumper::dump($properties),
                ];

                $viewPath = $this->getViewPath($view);

                $data['name']        = $component->getName();
                $data['view']        = Str::of($view->name())->replace('livewire.', '');
                $data['viewHandler'] = [
                    'handler' => IdeHandle::makeFileHandler($viewPath, '1'),
                    'path'    => (string) Str::of($viewPath)->replace(config('livewire.view_path') . '/', ''),
                    'line'    => 1,
                ];
                $data['viewPath']    = (string) Str::of($viewPath)->replace(config('livewire.view_path') . '/', '');
                $data['component']   = get_class($component);
                $data['id']          = $component->id;
                $data['dateTime']    = now()->format('H:i:s');

                $dumps = new LaraDumps(notificationId: $data['view']);

                $dumps->send(new LivewirePayload($data));

                $dumps->toScreen(
                    <<<HTML
<div class="w-full flex justify-between items-center space-x-2">
<span class="w-[1rem]">
<svg class="w-20 h-20" viewBox="0 0 234 54" xmlns:xlink="http://www.w3.org/1999/xlink">
   <defs>
      <path d="M6.21428571,3.96764549 L6.21428571,13.5302735 C6.21428571,15.2463011 4.82317047,16.6374164 3.10714286,16.6374164 C1.39111524,16.6374164 -2.95438243e-14,15.2463011 -2.97539771e-14,13.5302735 L-2.9041947e-14,1.98620229 C0.579922224,0.921664997 1.24240791,1.12585387e-13 2.43677218,1.0658141e-13 C4.3810703,1.0658141e-13 5.06039718,2.44244728 6.21428571,3.96764549 Z M17.952381,4.46584612 L17.952381,19.587619 C17.952381,21.4943164 16.4066974,23.04 14.5,23.04 C12.5933026,23.04 11.047619,21.4943164 11.047619,19.587619 L11.047619,2.47273143 C11.6977478,1.21920793 12.3678531,1.0658141e-13 13.7415444,1.0658141e-13 C15.916357,1.0658141e-13 16.5084695,3.05592831 17.952381,4.46584612 Z M29,4.18831009 L29,15.1664032 C29,16.8824308 27.6088848,18.2735461 25.8928571,18.2735461 C24.1768295,18.2735461 22.7857143,16.8824308 22.7857143,15.1664032 L22.7857143,1.67316044 C23.3267006,0.747223402 23.9709031,1.0658141e-13 25.0463166,1.0658141e-13 C27.0874587,1.0658141e-13 27.7344767,2.69181961 29,4.18831009 Z" id="path-100"></path>
      <path d="M6.21428571,6.89841791 C5.66311836,6.22351571 5.01068733,5.72269617 4.06708471,5.72269617 C1.82646191,5.72269617 1.41516964,8.5465388 1.66533454e-15,9.81963771 L4.4408921e-16,-2.36068323 C2.33936437e-16,-4.07671085 1.39111524,-5.46782609 3.10714286,-5.46782609 C4.82317047,-5.46782609 6.21428571,-4.07671085 6.21428571,-2.36068323 L6.21428571,6.89841791 Z M17.952381,7.11630262 C17.3645405,6.33416295 16.6773999,5.72269617 15.6347586,5.72269617 C13.1419388,5.72269617 12.9134319,9.21799873 11.047619,10.1843478 L11.047619,4.79760812 C11.047619,2.89091077 12.5933026,1.34522717 14.5,1.34522717 C16.4066974,1.34522717 17.952381,2.89091077 17.952381,4.79760812 L17.952381,7.11630262 Z M29,6.51179 C28.521687,6.04088112 27.9545545,5.72269617 27.2024325,5.72269617 C24.7875975,5.72269617 24.497619,9.0027269 22.7857143,10.086414 L22.7857143,-0.846671395 C22.7857143,-2.56269901 24.1768295,-3.95381425 25.8928571,-3.95381425 C27.6088848,-3.95381425 29,-2.56269901 29,-0.846671395 L29,6.51179 Z" id="path-300"></path>
   </defs>
   <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
      <g id="10.5″-iPad-Pro-Copy-6" transform="translate(-116.000000, -134.000000)">
         <g id="Group-3" transform="translate(115.000000, 136.000000)">
            <g id="Jelly" style="transform: translateY(3%);">
               <path d="M46.7606724,33.2469068 C45.9448607,34.4803214 45.3250477,36 43.6664081,36 C40.8749581,36 40.7240285,31.6956522 37.9310842,31.6956522 C35.1381399,31.6956522 35.2890695,36 32.4976195,36 C29.7061695,36 29.55524,31.6956522 26.7622957,31.6956522 C23.9693513,31.6956522 24.1202809,36 21.3288309,36 C18.537381,36 18.3864514,31.6956522 15.5935071,31.6956522 C12.8005628,31.6956522 12.9514923,36 10.1600424,36 C9.2827466,36 8.66625943,35.5748524 8.14660082,34.9917876 C6.14914487,31.5156333 5,27.4421238 5,23.0869565 C5,10.3363825 14.8497355,0 27,0 C39.1502645,0 49,10.3363825 49,23.0869565 C49,26.7327091 48.1947338,30.1810893 46.7606724,33.2469068 Z" id="Body-Copy-2" fill="#FB70A9"></path>
               <g id="Legs" transform="translate(12.000000, 27.000000)">
                  <mask id="mask-2" fill="white">
                     <use xlink:href="#path-100"></use>
                  </mask>
                  <use id="Combined-Shape" fill="#4E56A6" xlink:href="#path-100"></use>
                  <mask id="mask-4" fill="white">
                     <use xlink:href="#path-300"></use>
                  </mask>
                  <use id="Combined-Shape" fill-opacity="0.298513986" fill="#000000" xlink:href="#path-300"></use>
               </g>
               <path d="M46.7606724,33.2469068 C45.9448607,34.4803214 45.3250477,36 43.6664081,36 C40.8749581,36 40.7240285,31.6956522 37.9310842,31.6956522 C35.1381399,31.6956522 35.2890695,36 32.4976195,36 C29.7061695,36 29.55524,31.6956522 26.7622957,31.6956522 C23.9693513,31.6956522 24.1202809,36 21.3288309,36 C18.537381,36 18.3864514,31.6956522 15.5935071,31.6956522 C12.8005628,31.6956522 12.9514923,36 10.1600424,36 C9.2827466,36 8.66625943,35.5748524 8.14660082,34.9917876 C6.14914487,31.5156333 5,27.4421238 5,23.0869565 C5,10.3363825 14.8497355,0 27,0 C39.1502645,0 49,10.3363825 49,23.0869565 C49,26.7327091 48.1947338,30.1810893 46.7606724,33.2469068 Z" id="Body-Copy-4" fill="#FB70A9"></path>
               <path d="M42,35.5400931 C47.765228,26.9635183 47.9142005,17.4501539 42.4469174,7 C46.4994826,11.151687 49,16.849102 49,23.1355865 C49,26.7676093 48.1653367,30.203003 46.6789234,33.2572748 C45.8333297,34.4860445 45.1908898,36 43.4716997,36 C42.8832919,36 42.4080759,35.8226537 42,35.5400931 Z" id="Combined-Shape" fill="#E24CA6"></path>
               <g id="Eyes-Copy-2" transform="translate(0.000000, 6.000000)">
                  <path d="M25.8205128,22.8461538 C33.4710351,22.8461538 36.6923077,18.4078931 36.6923077,12.1048951 C36.6923077,5.80189712 31.8248393,0 25.8205128,0 C19.8161863,0 14.9487179,5.80189712 14.9487179,12.1048951 C14.9487179,18.4078931 18.1699905,22.8461538 25.8205128,22.8461538 Z" id="Oval" fill="#FFFFFF"></path>
                  <g id="Pupil" transform="translate(18.820513, 3.461538)">
                     <ellipse id="Oval" fill="#030776" cx="4.07692308" cy="4.5" rx="4.07692308" ry="4.5"></ellipse>
                     <ellipse id="Oval" fill="#FFFFFF" cx="3.3974359" cy="3.46153846" rx="2.03846154" ry="2.07692308"></ellipse>
                  </g>
               </g>
            </g>
         </g>
      </g>
   </g>
</svg>
</span>
<span>Livewire</span>
</div>
HTML,
                    false,
                    0,
                    'Livewire'
                );
            });
        }
    }

    private function getViewPath(View $view): string
    {
        $reflection = new ReflectionClass($view);
        $property   = $reflection->getProperty('path');
        $property->setAccessible(true);

        return strval($property->getValue($view));
    }

    public function isEnabled(): bool
    {
        return (bool) config('laradumps.send_livewire_components');
    }
}
