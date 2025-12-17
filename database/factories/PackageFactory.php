<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;


class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                    'Starter Pack',
                    'Premium Pack',
                    'Elite Pack',
                    'Business Pack',
                    'VIP Pack'
                ]),
            'benefits' => $this->faker->randomElement([
                'Enhanced security through decentralized data storage and cryptographic verification',
                'Full transparency with ledger records that can be audited by anyone at any time',
                'Reduced operational costs by removing intermediaries and automating workflows with smart contracts',
                'Faster and more efficient cross-border transactions using decentralized settlement systems',
                'Immutable data storage that prevents alterations, fraud, and unauthorized modifications',
                'Improved traceability for assets and transactions across complex supply chains',
                'Trustless interactions powered by consensus mechanisms instead of third-party authorities',
                'Automated execution of business logic using self-enforcing smart contracts',
                'Increased data integrity thanks to tamper-resistant distributed ledger technology',
                'Scalable blockchain infrastructure designed for high performance and enterprise-grade workloads',
            ]),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'active' => true,
        ];
    }
}
