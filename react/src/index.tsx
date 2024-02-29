import React,{lazy} from 'react';
import ReactDOM from 'react-dom/client';
import './index.css';
const Home = lazy(() => import('./router/App').then(module => ({default: module.Home})));

if(document.getElementById('Home') as HTMLElement){
  const root = ReactDOM.createRoot(document.getElementById('Home') as HTMLElement);
  root.render(
    <React.StrictMode>
      <Home />
    </React.StrictMode>
  );
}