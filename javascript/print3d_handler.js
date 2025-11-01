// 3D Print Handler - Add this to your 3dmodel_editor.js or include separately

// Initialize 3D Print Modal
document.addEventListener('DOMContentLoaded', function() {
    const print3DBtn = document.getElementById('print3DBtn');
    const print3DModal = new bootstrap.Modal(document.getElementById('print3DModal'));
    
    // Update infill value display
    document.getElementById('printInfill').addEventListener('input', function(e) {
        document.getElementById('infillValue').textContent = e.target.value + '%';
        updatePrintEstimates();
    });
    
    // Update scale value display
    document.getElementById('printScale').addEventListener('input', function(e) {
        document.getElementById('printScaleValue').textContent = e.target.value + '%';
        updatePrintEstimates();
    });
    
    // Update estimates when settings change
    document.getElementById('printMaterial').addEventListener('change', updatePrintEstimates);
    document.getElementById('printQuality').addEventListener('change', updatePrintEstimates);
    document.getElementById('printSupport').addEventListener('change', updatePrintEstimates);
    
    // Open 3D Print Modal
    print3DBtn.addEventListener('click', function() {
        // Check if a model is loaded and ready
        const hasModel = window.state && window.state.modelLoaded && window.state.loadedModel;
        
        if (!hasModel) {
            Swal.fire({
                icon: 'warning',
                title: 'No Model Loaded',
                text: 'Please load or generate a 3D model first.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }
        
        // Verify product information is available
        if (!PRODUCT_ID || !PRODUCT_NAME) {
            Swal.fire({
                icon: 'warning',
                title: 'Product Information Missing',
                html: 'Please make sure you are viewing a product model.<br>Go back to inventory and select a product to print.',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }
        
        console.log('[3D Print] Opening print modal...');
        console.log('[3D Print] Product ID:', PRODUCT_ID);
        console.log('[3D Print] Product Name:', PRODUCT_NAME);
        console.log('[3D Print] Has Model:', hasModel);
        
        print3DModal.show();
        updatePrintEstimates();
    });
    
    // Export STL Button
    document.getElementById('exportSTLBtn').addEventListener('click', function() {
        exportModelAsSTL();
    });
    
    // Send to Printer Button
    document.getElementById('sendToPrinterBtn').addEventListener('click', function() {
        sendToPrinter();
    });
});

// Calculate and update print estimates
function updatePrintEstimates() {
    const material = document.getElementById('printMaterial').value;
    const quality = document.getElementById('printQuality').value;
    const infill = parseInt(document.getElementById('printInfill').value);
    const scale = parseInt(document.getElementById('printScale').value) / 100;
    const support = document.getElementById('printSupport').value;
    
    // Material costs per gram (in PHP)
    const materialCosts = {
        'pla': 15,
        'abs': 18,
        'petg': 20,
        'tpu': 35,
        'nylon': 40,
        'resin': 50
    };
    
    // Quality settings (layer height affects print time)
    const qualityMultipliers = {
        'draft': 0.6,
        'normal': 1.0,
        'fine': 1.8,
        'ultra': 3.5
    };
    
    // Estimate model volume (simplified calculation)
    // In real implementation, you'd calculate actual model volume from geometry
    const baseVolume = 50; // cm³ (placeholder)
    const scaledVolume = baseVolume * Math.pow(scale, 3);
    
    // Calculate material weight (g) - PLA density ~1.24 g/cm³
    const density = 1.24;
    const infillFactor = infill / 100;
    const materialWeight = scaledVolume * density * infillFactor;
    
    // Add support material (if enabled)
    const supportWeight = support !== 'none' ? materialWeight * 0.2 : 0;
    const totalWeight = materialWeight + supportWeight;
    
    // Calculate cost
    const costPerGram = materialCosts[material] || 15;
    const totalCost = totalWeight * costPerGram;
    
    // Calculate print time (simplified)
    const baseTime = 120; // minutes (placeholder)
    const qualityMultiplier = qualityMultipliers[quality] || 1.0;
    const scaleTimeMultiplier = Math.pow(scale, 2);
    const supportTimeAddition = support !== 'none' ? 30 : 0;
    
    const totalMinutes = (baseTime * qualityMultiplier * scaleTimeMultiplier) + supportTimeAddition;
    const hours = Math.floor(totalMinutes / 60);
    const minutes = Math.round(totalMinutes % 60);
    
    // Update UI
    document.getElementById('estimatedPrintTime').textContent = 
        `${hours}h ${minutes}m (${totalWeight.toFixed(1)}g material)`;
    document.getElementById('estimatedCost').textContent = 
        `₱${totalCost.toFixed(2)}`;
}

// Export model as STL for 3D printing
function exportModelAsSTL() {
    if (!window.currentModel) {
        Swal.fire({
            icon: 'error',
            title: 'No Model',
            text: 'No model loaded to export.',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }
    
    Swal.fire({
        title: 'Preparing STL Export...',
        text: 'Processing model for 3D printing',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    try {
        // Import STLExporter from Three.js addons
        import('three/addons/exporters/STLExporter.js').then(module => {
            const STLExporter = module.STLExporter;
            const exporter = new STLExporter();
            
            // Get print settings
            const scale = parseInt(document.getElementById('printScale').value) / 100;
            
            // Clone and scale model
            const modelToExport = window.currentModel.clone();
            modelToExport.scale.set(scale, scale, scale);
            
            // Export as binary STL (more efficient)
            const stlBinary = exporter.parse(modelToExport, { binary: true });
            
            // Create blob and download
            const blob = new Blob([stlBinary], { type: 'application/octet-stream' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            
            // Generate filename with product info
            const timestamp = new Date().toISOString().slice(0, 10);
            const productName = PRODUCT_NAME ? PRODUCT_NAME.replace(/\s+/g, '_') : 'model';
            const productIdStr = PRODUCT_ID ? `_ID${PRODUCT_ID}` : '';
            link.download = `${productName}${productIdStr}_3DPrint_${timestamp}.stl`;
            
            link.click();
            URL.revokeObjectURL(url);
            
            Swal.fire({
                icon: 'success',
                title: 'STL Exported!',
                html: `<p>Your model is ready for 3D printing.</p>
                       <p><strong>Product:</strong> ${PRODUCT_NAME || 'Unknown'}</p>
                       ${PRODUCT_ID ? `<p><strong>Product ID:</strong> ${PRODUCT_ID}</p>` : ''}`,
                confirmButtonColor: '#0d6efd'
            });
            
            // Log export details
            logToConsole(`STL exported: ${link.download}`);
            logToConsole(`Product ID: ${PRODUCT_ID || 'N/A'}`);
            logToConsole(`Product Name: ${PRODUCT_NAME || 'N/A'}`);
            logToConsole(`Scale: ${scale * 100}%`);
            logToConsole(`Material: ${document.getElementById('printMaterial').value.toUpperCase()}`);
            
        }).catch(error => {
            console.error('STL Export Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Export Failed',
                text: 'Failed to export STL file. Please try again.',
                confirmButtonColor: '#0d6efd'
            });
        });
        
    } catch (error) {
        console.error('Export error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Export Error',
            text: 'An error occurred during export.',
            confirmButtonColor: '#0d6efd'
        });
    }
}

// Send model to 3D printer (or print service)
function sendToPrinter() {
    if (!window.currentModel) {
        Swal.fire({
            icon: 'error',
            title: 'No Model',
            text: 'No model loaded to print.',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }
    
    // Verify product information
    if (!PRODUCT_ID || !PRODUCT_NAME) {
        Swal.fire({
            icon: 'error',
            title: 'Product Information Missing',
            text: 'Cannot submit print job without product information.',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }
    
    // Collect print settings
    const printSettings = {
        product_id: PRODUCT_ID, // ✅ Now properly captured from URL
        product_name: PRODUCT_NAME,
        product_image_path: PRODUCT_IMAGE_PATH || null,
        material: document.getElementById('printMaterial').value,
        quality: document.getElementById('printQuality').value,
        infill: document.getElementById('printInfill').value,
        support: document.getElementById('printSupport').value,
        scale: document.getElementById('printScale').value,
        orientation: document.getElementById('printOrientation').value,
        raft: document.getElementById('printRaft').checked,
        brim: document.getElementById('printBrim').checked,
        hollow: document.getElementById('hollowModel').checked,
        notes: document.getElementById('printNotes').value,
        estimated_time: document.getElementById('estimatedPrintTime').textContent,
        estimated_cost: document.getElementById('estimatedCost').textContent
    };
    
    Swal.fire({
        title: 'Send to 3D Printer?',
        html: `
            <div style="text-align: left; margin: 15px 0;">
                <p><strong>Product:</strong> ${printSettings.product_name}</p>
                <p><strong>Product ID:</strong> ${printSettings.product_id}</p>
                <hr style="border-color: #ddd; margin: 15px 0;">
                <p><strong>Material:</strong> ${printSettings.material.toUpperCase()}</p>
                <p><strong>Quality:</strong> ${printSettings.quality}</p>
                <p><strong>Infill:</strong> ${printSettings.infill}%</p>
                <p><strong>Scale:</strong> ${printSettings.scale}%</p>
                <p><strong>Estimated Time:</strong> ${printSettings.estimated_time}</p>
                <p><strong>Estimated Cost:</strong> ${printSettings.estimated_cost}</p>
            </div>
            <p style="margin-top: 15px;">This will send the print job to the connected 3D printer or print service.</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fa fa-print"></i> Send to Printer',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            processPrintJob(printSettings);
        }
    });
}

// Process the print job
function processPrintJob(settings) {
    Swal.fire({
        title: 'Processing Print Job...',
        html: `<p>Preparing your model for 3D printing</p>
               <p><strong>Product:</strong> ${settings.product_name}</p>
               <p><strong>Product ID:</strong> ${settings.product_id}</p>`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Send to backend
    fetch('3D-print/submit_3d_print.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(settings)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Print Job Submitted!',
                html: `
                    <div style="text-align: left; margin: 15px auto; max-width: 400px;">
                        <p>Your 3D print job has been queued successfully.</p>
                        <hr style="border-color: #ddd; margin: 15px 0;">
                        <p><strong>Job ID:</strong> ${data.job_id || 'N/A'}</p>
                        <p><strong>Product:</strong> ${settings.product_name}</p>
                        <p><strong>Product ID:</strong> ${settings.product_id}</p>
                        <p><strong>Status:</strong> ${data.status || 'Pending'}</p>
                        <p><strong>Estimated Time:</strong> ${settings.estimated_time}</p>
                        <p><strong>Estimated Cost:</strong> ${settings.estimated_cost}</p>
                        <hr style="border-color: #ddd; margin: 15px 0;">
                        <p style="color: #6c757d; font-size: 0.9rem;">You will be notified when printing starts.</p>
                    </div>
                `,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'OK'
            });
            
            logToConsole('Print job submitted successfully');
            logToConsole(`Job ID: ${data.job_id || 'N/A'}`);
            logToConsole(`Product ID: ${settings.product_id}`);
            logToConsole(`Product Name: ${settings.product_name}`);
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('print3DModal')).hide();
        } else {
            throw new Error(data.message || 'Failed to submit print job');
        }
    })
    .catch(error => {
        console.error('Print submission error:', error);
        
        Swal.fire({
            icon: 'error',
            title: 'Submission Failed',
            html: `
                <p>Failed to submit print job to server.</p>
                <p><strong>Error:</strong> ${error.message}</p>
                <hr style="border-color: #ddd; margin: 15px 0;">
                <p style="color: #6c757d; font-size: 0.9rem;">
                    You can still export the model as STL and print it locally.
                </p>
            `,
            confirmButtonColor: '#0d6efd'
        });
        
        logToConsole('Print submission failed: ' + error.message);
    });
}

// Helper function to log to console (if available in your editor)
function logToConsole(message) {
    if (typeof window.logToConsole === 'function') {
        window.logToConsole(message);
    }
    console.log(`[3D Print] ${message}`);
}