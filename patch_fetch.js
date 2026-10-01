const fs = require('fs');
const path = require('path');

const appJsxPath = path.join('/Users/rajivranjan/Documents/Work/rcyconstruction-fe/src', 'App.jsx');
let appJsx = fs.readFileSync(appJsxPath, 'utf8');

if (!appJsx.includes('import { Toaster, toast } from')) {
    appJsx = appJsx.replace("import { ThemeProvider } from './contexts/ThemeContext';", "import { ThemeProvider } from './contexts/ThemeContext';\nimport { Toaster, toast } from 'react-hot-toast';");
    
    const fetchOverride = `
const originalFetch = window.fetch;
window.fetch = async (...args) => {
  const [url, options] = args;
  const isMutation = options && options.method && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(options.method.toUpperCase());
  
  try {
    const response = await originalFetch(...args);
    if (isMutation) {
      // Don't toast if the response has a 422 (validation error) since the form handles it, 
      // but the user wants "failed like that". Let's toast anyway.
      if (response.ok) {
         if (url.toString().includes('/login')) {
             toast.success('Login successful!');
         } else if (options.method === 'DELETE') {
             toast.success('Deleted successfully!');
         } else {
             toast.success('Saved successfully!');
         }
      } else {
         // Attempt to read error message if we want, but simple is better
         toast.error('Operation failed!');
      }
    }
    return response;
  } catch (error) {
    if (isMutation) toast.error('Network error!');
    throw error;
  }
};
`;
    
    appJsx = appJsx.replace("function App() {", fetchOverride + "\nfunction App() {");
    
    appJsx = appJsx.replace("<ThemeProvider>", "<ThemeProvider>\n      <Toaster position=\"top-right\" />");
    
    fs.writeFileSync(appJsxPath, appJsx);
    console.log("App.jsx patched successfully.");
}
