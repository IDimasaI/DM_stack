import React,{lazy} from 'react';
import ReactDOM from 'react-dom/client';
import './index.css';
const Constellation_system = lazy(() => import('./router/App').then(module => ({default: module.Constellation_system})));
const Regions = lazy(() => import('./router/App').then(module => ({default: module.Region_all})));
if(document.getElementById('Constellation&System') as HTMLElement){
  const root = ReactDOM.createRoot(document.getElementById('Constellation&System') as HTMLElement);
  root.render(
    <React.StrictMode>
      <Constellation_system />
    </React.StrictMode>
  );
}
if(document.getElementById('Regions') as HTMLElement){
  const root = ReactDOM.createRoot(document.getElementById('Regions') as HTMLElement);
  root.render(
    <React.StrictMode>
      <Regions />
    </React.StrictMode>
  );
}