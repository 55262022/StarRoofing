import React, { Component } from 'react';

class InventoryIntegration extends Component {
    constructor() {
        super();
        this.state = {
            products: [],
            error: null,
        };
    }

    componentDidMount() {
        this.fetchInventoryData();
    }

    fetchInventoryData = async () => {
        try {
            const response = await fetch('../path/to/inventory.php'); // Update with the correct path
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            this.setState({ products: data });
        } catch (error) {
            this.setState({ error: error.message });
        }
    };

    updateInventory = async (estimatedMaterials) => {
        try {
            const response = await fetch('../path/to/inventory.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(estimatedMaterials),
            });
            if (!response.ok) {
                throw new Error('Failed to update inventory');
            }
            const updatedData = await response.json();
            this.setState({ products: updatedData });
        } catch (error) {
            this.setState({ error: error.message });
        }
    };

    render() {
        const { products, error } = this.state;

        return (
            <div>
                {error && <p>Error: {error}</p>}
                <h2>Inventory Products</h2>
                <ul>
                    {products.map(product => (
                        <li key={product.id}>{product.name} - {product.stock_quantity}</li>
                    ))}
                </ul>
            </div>
        );
    }
}

export default InventoryIntegration;